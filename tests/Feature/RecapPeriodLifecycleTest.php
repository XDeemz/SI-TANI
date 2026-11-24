<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapItem;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapPeriodLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function createContractSetup(array $periodAttributes = []): array
    {
        $farmer = User::factory()->create(['name' => 'Petani Budi']);
        $trader = User::factory()->create(['name' => 'Pedagang Agus']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Cabai Rawit Merah',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 60,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40,
            'offered_price' => 30000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create(array_merge([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 40,
            'status' => 'ready',
            'total_amount' => 0.00,
        ], $periodAttributes));

        return compact('farmer', 'trader', 'commodity', 'offer', 'period');
    }

    public function test_unauthenticated_user_cannot_start_or_close_period(): void
    {
        ['period' => $period] = $this->createContractSetup();

        $this->post("/rekap/{$period->id}/start")->assertRedirect('/login');
        $this->patch("/rekap/{$period->id}/start")->assertRedirect('/login');

        $this->post("/rekap/{$period->id}/close")->assertRedirect('/login');
        $this->patch("/rekap/{$period->id}/close")->assertRedirect('/login');
    }

    public function test_trader_can_start_period_from_ready_to_active(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'ready']);

        $this->assertNull($period->started_at);
        $this->assertEquals('ready', $period->status);

        $response = $this->actingAs($trader)->post("/rekap/{$period->id}/start");
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('active', $period->status);
        $this->assertNotNull($period->started_at);

        // Akses via alias route /recap-periods/{id}/start juga didukung
        ['trader' => $trader2, 'period' => $period2] = $this->createContractSetup(['status' => 'ready']);
        $response2 = $this->actingAs($trader2)->patch("/recap-periods/{$period2->id}/start");
        $response2->assertSessionHas('success');
        $period2->refresh();
        $this->assertEquals('active', $period2->status);
    }

    public function test_trader_can_close_period_from_active_to_closed_with_accurate_total_amount(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup([
            'status' => 'active',
            'started_at' => now()->subDays(3),
        ]);

        // Catat setoran panen
        RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => now()->subDays(2)->toDateString(),
            'net_weight' => 20.00,
            'actual_price' => 30000.00,
            'subtotal' => 600000.00,
            'notes' => 'Panen petikan pertama',
        ]);

        RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => now()->subDay()->toDateString(),
            'net_weight' => 25.50,
            'actual_price' => 30000.00,
            'subtotal' => 765000.00,
            'notes' => 'Panen petikan kedua',
        ]);

        $expectedTotal = 600000.00 + 765000.00; // 1.365.000,00

        $response = $this->actingAs($trader)->post("/rekap/{$period->id}/close");
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('closed', $period->status);
        $this->assertNotNull($period->ended_at);
        $this->assertEquals($expectedTotal, (float) $period->total_amount);
    }

    public function test_deal_cancellation_scenario_trader_can_start_and_immediately_close_without_items(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'ready']);

        // Pedagang memulai periode
        $this->actingAs($trader)->post("/rekap/{$period->id}/start")->assertSessionHas('success');
        $period->refresh();
        $this->assertEquals('active', $period->status);

        // Skenario pembatalan kesepakatan: tidak ada item rekap sama sekali (0 transaksi)
        $this->assertEquals(0, $period->recapItems()->count());

        // Pedagang mengakhiri periode tanpa item
        $this->actingAs($trader)->post("/rekap/{$period->id}/close")->assertSessionHas('success');
        $period->refresh();

        $this->assertEquals('closed', $period->status);
        $this->assertNotNull($period->ended_at);
        $this->assertEquals(0.00, (float) $period->total_amount);
    }

    public function test_farmer_cannot_start_or_close_period(): void
    {
        ['farmer' => $farmer, 'period' => $period] = $this->createContractSetup(['status' => 'ready']);

        // Petani mencoba start
        $this->actingAs($farmer)->post("/rekap/{$period->id}/start")->assertForbidden();
        $period->refresh();
        $this->assertEquals('ready', $period->status);

        // Ubah status ke active
        $period->update(['status' => 'active', 'started_at' => now()]);

        // Petani mencoba close
        $this->actingAs($farmer)->post("/rekap/{$period->id}/close")->assertForbidden();
        $period->refresh();
        $this->assertEquals('active', $period->status);
    }

    public function test_third_party_user_cannot_start_or_close_period(): void
    {
        ['period' => $period] = $this->createContractSetup(['status' => 'ready']);
        $stranger = User::factory()->create();

        // Pihak ketiga mencoba start
        $this->actingAs($stranger)->post("/rekap/{$period->id}/start")->assertForbidden();
        $period->refresh();
        $this->assertEquals('ready', $period->status);

        // Ubah status ke active
        $period->update(['status' => 'active', 'started_at' => now()]);

        // Pihak ketiga mencoba close
        $this->actingAs($stranger)->post("/rekap/{$period->id}/close")->assertForbidden();
        $period->refresh();
        $this->assertEquals('active', $period->status);
    }

    public function test_invalid_status_transitions_are_forbidden(): void
    {
        ['trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'ready']);

        // Pedagang mencoba close saat masih 'ready' -> ditolak 403
        $this->actingAs($trader)->post("/rekap/{$period->id}/close")->assertForbidden();
        $period->refresh();
        $this->assertEquals('ready', $period->status);

        // Transisi ke 'active'
        $period->update(['status' => 'active', 'started_at' => now()]);

        // Pedagang mencoba start saat sudah 'active' -> ditolak 403
        $this->actingAs($trader)->post("/rekap/{$period->id}/start")->assertForbidden();

        // Transisi ke 'closed'
        $period->update(['status' => 'closed', 'ended_at' => now()]);

        // Pedagang mencoba start saat sudah 'closed' -> ditolak 403
        $this->actingAs($trader)->post("/rekap/{$period->id}/start")->assertForbidden();

        // Pedagang mencoba close lagi saat sudah 'closed' -> ditolak 403
        $this->actingAs($trader)->post("/rekap/{$period->id}/close")->assertForbidden();

        // Transisi ke 'paid'
        $period->update(['status' => 'paid', 'paid_at' => now()]);

        // Pedagang mencoba start dan close saat sudah 'paid' -> ditolak 403
        $this->actingAs($trader)->post("/rekap/{$period->id}/start")->assertForbidden();
        $this->actingAs($trader)->post("/rekap/{$period->id}/close")->assertForbidden();
    }

    public function test_views_show_correct_lifecycle_buttons_and_timestamps(): void
    {
        ['farmer' => $farmer, 'trader' => $trader, 'period' => $period] = $this->createContractSetup(['status' => 'ready']);

        // 1. Status 'ready': Pedagang melihat tombol Mulai Periode
        $traderReadyView = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderReadyView->assertOk();
        $traderReadyView->assertSeeText('Mulai Periode');
        $traderReadyView->assertDontSeeText('Akhiri Periode');

        // Petani melihat status Siap Dimulai (read-only)
        $farmerReadyView = $this->actingAs($farmer)->get("/rekap/{$period->id}");
        $farmerReadyView->assertOk();
        $farmerReadyView->assertSeeText('Siap Dimulai');
        $farmerReadyView->assertDontSeeText('Mulai Periode');

        // 2. Status 'active': Pedagang melihat tombol Akhiri Periode
        $period->update(['status' => 'active', 'started_at' => now()]);

        $traderActiveView = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderActiveView->assertOk();
        $traderActiveView->assertSeeText('Akhiri Periode');
        $traderActiveView->assertDontSeeText('Mulai Periode');

        // Petani melihat status Periode Aktif Berjalan
        $farmerActiveView = $this->actingAs($farmer)->get("/rekap/{$period->id}");
        $farmerActiveView->assertOk();
        $farmerActiveView->assertSeeText('Periode Aktif Berjalan');
        $farmerActiveView->assertDontSeeText('Akhiri Periode');

        // 3. Status 'closed': Keduanya melihat status Menunggu Pembayaran dan info terkunci
        $period->update(['status' => 'closed', 'ended_at' => now(), 'total_amount' => 500000.00]);

        $traderClosedView = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderClosedView->assertOk();
        $traderClosedView->assertSeeText('Menunggu Pembayaran');
        $traderClosedView->assertDontSeeText('Mulai Periode');
        $traderClosedView->assertDontSeeText('Akhiri Periode');
        $traderClosedView->assertSeeText('500.000,00');

        $farmerClosedView = $this->actingAs($farmer)->get("/rekap/{$period->id}");
        $farmerClosedView->assertOk();
        $farmerClosedView->assertSeeText('Menunggu Pembayaran');
        $farmerClosedView->assertDontSeeText('Mulai Periode');
        $farmerClosedView->assertDontSeeText('Akhiri Periode');
        $farmerClosedView->assertSeeText('500.000,00');
    }
}
