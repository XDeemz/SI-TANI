<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapItem;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapDailyItemSheetTest extends TestCase
{
    use RefreshDatabase;

    private function createContractSetup(array $periodAttributes = []): array
    {
        $farmer = User::factory()->create([
            'name' => 'Petani Sari',
            'payment_info' => 'BRI 1234-5678-90 a.n. Sari / QRIS tersedia',
        ]);
        $trader = User::factory()->create(['name' => 'Pedagang Jaya']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Tomat Segar',
            'unit' => 'kg',
            'initial_daily_capacity' => 150,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(3)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50,
            'offered_price' => 12000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create(array_merge([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 50,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'total_amount' => 0.00,
        ], $periodAttributes));

        return compact('farmer', 'trader', 'commodity', 'offer', 'period');
    }

    public function test_unauthenticated_user_cannot_mutate_recap_items(): void
    {
        ['period' => $period] = $this->createContractSetup();
        $item = RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => '2026-11-20',
            'net_weight' => 10,
            'actual_price' => 10000,
            'subtotal' => 100000,
        ]);

        $this->post("/rekap/{$period->id}/items", [
            'transaction_date' => '2026-11-21',
            'net_weight' => 10,
            'actual_price' => 10000,
        ])->assertRedirect('/login');

        $this->patch("/rekap/{$period->id}/items/{$item->id}", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 12,
            'actual_price' => 10000,
        ])->assertRedirect('/login');

        $this->delete("/rekap/{$period->id}/items/{$item->id}")->assertRedirect('/login');
    }

    public function test_trader_can_store_daily_recap_item_with_two_decimal_precision(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup();

        // Bobot 12.345 * harga 10000.33 = 123454.07385 -> 123454.07
        $response = $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 12.345,
            'actual_price' => 10000.33,
            'notes' => 'Pengiriman pagi (H+1 pencatatan)',
        ]);

        $response->assertSessionHas('success');

        $item = RecapItem::where('recap_period_id', $period->id)->firstOrFail();
        $this->assertSame('123454.07', (string) $item->subtotal);
        $this->assertSame('Pengiriman pagi (H+1 pencatatan)', $item->notes);

        // Tambah baris kedua untuk verifikasi akumulasi total_amount
        $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
            'transaction_date' => '2026-11-21',
            'net_weight' => 25.50,
            'actual_price' => 12500.55,
            'notes' => null,
        ])->assertSessionHas('success');

        // 25.50 * 12500.55 = 318764.025 -> round = 318764.03
        // Total = 123454.07 + 318764.03 = 442218.10
        $period->refresh();
        $this->assertSame('442218.10', (string) $period->total_amount);
    }

    public function test_trader_can_record_holiday_with_zero_net_weight(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup();

        $response = $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
            'transaction_date' => '2026-11-22',
            'net_weight' => 0,
            'actual_price' => 12000,
            'notes' => 'Hari libur panen',
        ]);

        $response->assertSessionHas('success');

        $item = RecapItem::where('recap_period_id', $period->id)->firstOrFail();
        $this->assertSame('0.00', (string) $item->net_weight);
        $this->assertSame('0.00', (string) $item->subtotal);

        $period->refresh();
        $this->assertSame('0.00', (string) $period->total_amount);
    }

    public function test_duplicate_transaction_date_is_rejected_in_same_period_on_store_and_update_but_allowed_across_periods(): void
    {
        ['farmer' => $farmer, 'commodity' => $commodity, 'trader' => $trader, 'period' => $period1] = $this->createContractSetup();

        // Periode lain dengan pedagang kedua pada komoditas yang sama
        $trader2 = User::factory()->create(['name' => 'Pedagang Kedua']);
        $offer2 = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader2->id,
            'offered_daily_capacity' => 30,
            'offered_price' => 13000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);
        $period2 = RecapPeriod::create([
            'commodity_offer_id' => $offer2->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader2->id,
            'locked_capacity' => 30,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'total_amount' => 0.00,
        ]);

        // Simpan tanggal 2026-11-20 di periode 1
        $this->actingAs($trader)->post("/rekap/{$period1->id}/items", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 10,
            'actual_price' => 12000,
        ])->assertSessionHas('success');

        // Duplikasi tanggal yang sama di periode 1 saat store ditolak dengan error validasi
        $this->actingAs($trader)->post("/rekap/{$period1->id}/items", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 15,
            'actual_price' => 12000,
        ])->assertSessionHasErrors('transaction_date');

        $this->assertEquals(1, $period1->recapItems()->count());

        // Tambah tanggal berbeda (2026-11-21) di periode 1 -> berhasil
        $this->actingAs($trader)->post("/rekap/{$period1->id}/items", [
            'transaction_date' => '2026-11-21',
            'net_weight' => 20,
            'actual_price' => 12000,
        ])->assertSessionHas('success');

        $secondItem = $period1->recapItems()->whereDate('transaction_date', '2026-11-21')->firstOrFail();

        // Update item kedua menjadi tanggal 2026-11-20 (milik item pertama di periode yang sama) -> ditolak
        $this->actingAs($trader)->patch("/rekap/{$period1->id}/items/{$secondItem->id}", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 22,
            'actual_price' => 12000,
        ])->assertSessionHasErrors('transaction_date');

        // Update item kedua dengan tanggalnya sendiri (ignore diri sendiri) -> berhasil
        $this->actingAs($trader)->patch("/rekap/{$period1->id}/items/{$secondItem->id}", [
            'transaction_date' => '2026-11-21',
            'net_weight' => 22,
            'actual_price' => 12000,
        ])->assertSessionHas('success');

        // Tanggal yang sama (2026-11-20) di periode berbeda (period2) -> diizinkan
        $this->actingAs($trader2)->post("/rekap/{$period2->id}/items", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 30,
            'actual_price' => 13000,
        ])->assertSessionHas('success');

        $this->assertEquals(1, $period2->recapItems()->count());
    }

    public function test_trader_can_update_and_destroy_recap_item_and_sync_total_amount(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup();

        $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 10,
            'actual_price' => 10000,
            'notes' => 'Awal',
        ])->assertSessionHas('success');

        $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
            'transaction_date' => '2026-11-21',
            'net_weight' => 20,
            'actual_price' => 15000,
            'notes' => 'Hari 2',
        ])->assertSessionHas('success');

        $period->refresh();
        $this->assertSame('400000.00', (string) $period->total_amount);

        $item1 = $period->recapItems()->whereDate('transaction_date', '2026-11-20')->firstOrFail();

        // Update item 1: 15.25 * 11000.50 = 167757.625 -> 167757.63; total = 467757.63
        $this->actingAs($trader)->patch("/rekap/{$period->id}/items/{$item1->id}", [
            'transaction_date' => '2026-11-19',
            'net_weight' => 15.25,
            'actual_price' => 11000.50,
            'notes' => 'Koreksi tanggal & bobot',
        ])->assertSessionHas('success');

        $item1->refresh();
        $period->refresh();
        $this->assertSame('167757.63', (string) $item1->subtotal);
        $this->assertSame('Koreksi tanggal & bobot', $item1->notes);
        $this->assertSame('467757.63', (string) $period->total_amount);

        // Hapus item 1 -> sisa item 2 (300000.00)
        $this->actingAs($trader)->delete("/rekap/{$period->id}/items/{$item1->id}")
            ->assertSessionHas('success');

        $period->refresh();
        $this->assertDatabaseMissing('recap_items', ['id' => $item1->id]);
        $this->assertSame('300000.00', (string) $period->total_amount);
    }

    public function test_backend_locking_forbids_mutations_when_period_is_not_active(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'active']);

        $item = RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => '2026-11-20',
            'net_weight' => 10,
            'actual_price' => 10000,
            'subtotal' => 100000,
        ]);

        foreach (['ready', 'closed', 'paid'] as $lockedStatus) {
            $period->update(['status' => $lockedStatus]);

            $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
                'transaction_date' => '2026-11-25',
                'net_weight' => 10,
                'actual_price' => 10000,
            ])->assertForbidden();

            $this->actingAs($trader)->patch("/rekap/{$period->id}/items/{$item->id}", [
                'transaction_date' => '2026-11-20',
                'net_weight' => 99,
                'actual_price' => 10000,
            ])->assertForbidden();

            $this->actingAs($trader)->delete("/rekap/{$period->id}/items/{$item->id}")
                ->assertForbidden();
        }

        $item->refresh();
        $this->assertSame('10.00', (string) $item->net_weight);
        $this->assertDatabaseHas('recap_items', ['id' => $item->id]);
    }

    public function test_farmer_third_party_and_cross_period_item_mutations_are_forbidden(): void
    {
        ['farmer' => $farmer, 'commodity' => $commodity, 'trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'active']);
        $stranger = User::factory()->create();

        $item = RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => '2026-11-20',
            'net_weight' => 10,
            'actual_price' => 10000,
            'subtotal' => 100000,
        ]);

        // Petani dan pihak ketiga ditolak 403
        foreach ([$farmer, $stranger] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)->post("/rekap/{$period->id}/items", [
                'transaction_date' => '2026-11-21',
                'net_weight' => 5,
                'actual_price' => 10000,
            ])->assertForbidden();

            $this->actingAs($unauthorizedUser)->patch("/rekap/{$period->id}/items/{$item->id}", [
                'transaction_date' => '2026-11-20',
                'net_weight' => 5,
                'actual_price' => 10000,
            ])->assertForbidden();

            $this->actingAs($unauthorizedUser)->delete("/rekap/{$period->id}/items/{$item->id}")
                ->assertForbidden();
        }

        // Pedagang lain pada periode lain tidak dapat memutasi item milik periode berbeda
        $otherTrader = User::factory()->create();
        $otherOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $otherTrader->id,
            'offered_daily_capacity' => 20,
            'offered_price' => 11000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);
        $otherPeriod = RecapPeriod::create([
            'commodity_offer_id' => $otherOffer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $otherTrader->id,
            'locked_capacity' => 20,
            'status' => 'active',
            'started_at' => now(),
            'total_amount' => 0.00,
        ]);

        $this->actingAs($otherTrader)->patch("/rekap/{$otherPeriod->id}/items/{$item->id}", [
            'transaction_date' => '2026-11-20',
            'net_weight' => 5,
            'actual_price' => 10000,
        ])->assertForbidden();

        $this->actingAs($otherTrader)->delete("/rekap/{$otherPeriod->id}/items/{$item->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('recap_items', ['id' => $item->id]);
    }

    public function test_ui_shows_sheet_controls_only_for_active_trader_and_read_only_for_farmer_and_locked_periods(): void
    {
        ['farmer' => $farmer, 'trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'active']);

        RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => '2026-11-20',
            'net_weight' => 0.00,
            'actual_price' => 12000.00,
            'subtotal' => 0.00,
            'notes' => 'Hari libur',
        ]);

        // Pedagang saat active: form tambah cepat, tombol Edit, tombol Hapus muncul
        $traderActive = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderActive->assertOk();
        $traderActive->assertSeeText('Tambah Baris Rekap Harian');
        $traderActive->assertSeeText('+ Tambah Baris');
        $traderActive->assertSeeText('Edit');
        $traderActive->assertSeeText('Hapus');
        $traderActive->assertSeeText('Libur');

        // Petani saat active: read-only + info pembayaran petani tampil, tanpa kontrol mutasi
        $farmerActive = $this->actingAs($farmer)->get("/rekap/{$period->id}");
        $farmerActive->assertOk();
        $farmerActive->assertSeeText('Mode Pantau Petani (Read-Only)');
        $farmerActive->assertSeeText('BRI 1234-5678-90 a.n. Sari / QRIS tersedia');
        $farmerActive->assertDontSeeText('Tambah Baris Rekap Harian');
        $farmerActive->assertDontSeeText('+ Tambah Baris');
        $farmerActive->assertDontSeeText('Edit Baris Rekap Harian');

        // Pedagang saat closed: seluruh form tambah, tombol edit, dan tombol hapus disembunyikan
        $period->update(['status' => 'closed', 'ended_at' => now()]);
        $traderClosed = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderClosed->assertOk();
        $traderClosed->assertSeeText('Sheet Rekap Terkunci Permanen');
        $traderClosed->assertDontSeeText('Tambah Baris Rekap Harian');
        $traderClosed->assertDontSeeText('+ Tambah Baris');
        $traderClosed->assertDontSeeText('Edit Baris Rekap Harian');
    }

    public function test_validation_rejects_negative_numbers_invalid_date_and_overlong_notes(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup();

        $this->actingAs($trader)->post("/rekap/{$period->id}/items", [
            'transaction_date' => 'bukan-tanggal',
            'net_weight' => -1,
            'actual_price' => -500,
            'notes' => str_repeat('a', 256),
        ])->assertSessionHasErrors(['transaction_date', 'net_weight', 'actual_price', 'notes']);

        $this->assertEquals(0, $period->recapItems()->count());
    }

    public function test_alias_routes_and_chronological_ordering_work_as_expected(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup();

        // Input tanggal lebih akhir terlebih dahulu via route alias /recap-periods
        $this->actingAs($trader)->post("/recap-periods/{$period->id}/items", [
            'transaction_date' => '2026-11-25',
            'net_weight' => 10,
            'actual_price' => 10000,
            'notes' => 'Catatan Kedua Secara Kronologis',
        ])->assertSessionHas('success');

        $this->actingAs($trader)->post("/recap-periods/{$period->id}/items", [
            'transaction_date' => '2026-11-18',
            'net_weight' => 5,
            'actual_price' => 10000,
            'notes' => 'Catatan Pertama Secara Kronologis',
        ])->assertSessionHas('success');

        $response = $this->actingAs($trader)->get("/recap-periods/{$period->id}");
        $response->assertOk();
        $response->assertSeeTextInOrder([
            'Catatan Pertama Secara Kronologis',
            'Catatan Kedua Secara Kronologis',
        ]);

        $item = $period->recapItems()->whereDate('transaction_date', '2026-11-18')->firstOrFail();

        // Update dan delete via route alias /recap-periods
        $this->actingAs($trader)->patch("/recap-periods/{$period->id}/items/{$item->id}", [
            'transaction_date' => '2026-11-18',
            'net_weight' => 8,
            'actual_price' => 10000,
            'notes' => 'Catatan Diperbarui',
        ])->assertSessionHas('success');

        $this->assertSame('180000.00', (string) $period->fresh()->total_amount);

        $this->actingAs($trader)->delete("/recap-periods/{$period->id}/items/{$item->id}")
            ->assertSessionHas('success');

        $this->assertSame('100000.00', (string) $period->fresh()->total_amount);

        // Hapus item terakhir -> total_amount kembali ke 0.00
        $lastItem = $period->recapItems()->firstOrFail();
        $this->actingAs($trader)->delete("/recap-periods/{$period->id}/items/{$lastItem->id}")
            ->assertSessionHas('success');

        $this->assertSame('0.00', (string) $period->fresh()->total_amount);
    }
}
