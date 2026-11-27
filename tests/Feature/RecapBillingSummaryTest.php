<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapBillingSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup(array $farmerAttributes = [], array $periodAttributes = []): array
    {
        $farmer = User::factory()->create(array_merge([
            'name' => 'Petani Makmur',
            'phone' => '081234567890',
            'payment_info' => "BCA 1234567890 a.n. Petani Makmur\nQRIS tersedia saat serah terima",
        ], $farmerAttributes));

        $trader = User::factory()->create([
            'name' => 'Pedagang Sejahtera',
            'phone' => '089876543210',
        ]);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Cabai Merah Keriting',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 60.00,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40.00,
            'offered_price' => 28000.00,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create(array_merge([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 40.00,
            'status' => 'active',
            'started_at' => now()->subDays(2),
            'total_amount' => 1250500.75,
        ], $periodAttributes));

        return compact('farmer', 'trader', 'commodity', 'offer', 'period');
    }

    public function test_show_displays_total_amount_in_two_decimal_rupiah_format_for_trader_and_farmer(): void
    {
        ['farmer' => $farmer, 'trader' => $trader, 'period' => $period] = $this->createSetup([], [
            'status' => 'active',
            'total_amount' => 1250500.75,
        ]);

        foreach ([$trader, $farmer] as $viewer) {
            $response = $this->actingAs($viewer)->get("/rekap/{$period->id}");
            $response->assertOk();
            $response->assertSeeText('Ringkasan Tagihan & Informasi Pembayaran');
            $response->assertSeeText('Rp 1.250.500,75');
            $response->assertSeeText('Estimasi Berjalan');
        }

        // Saat closed/paid, keterangan berubah menjadi Tagihan Final
        $period->update([
            'status' => 'closed',
            'ended_at' => now(),
            'total_amount' => 2500000.00,
        ]);

        $closedResponse = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $closedResponse->assertOk();
        $closedResponse->assertSeeText('Rp 2.500.000,00');
        $closedResponse->assertSeeText('Tagihan Final');
    }

    public function test_show_and_index_display_accurate_status_labels_across_all_lifecycle_states(): void
    {
        ['farmer' => $farmer, 'trader' => $trader, 'period' => $period] = $this->createSetup([], [
            'status' => 'ready',
            'started_at' => null,
            'total_amount' => 0.00,
        ]);

        $expectedLabels = [
            'ready' => 'Siap Dimulai',
            'active' => 'Aktif',
            'closed' => 'Menunggu Pembayaran',
            'paid' => 'Lunas / Selesai',
        ];

        foreach ($expectedLabels as $status => $label) {
            $updates = ['status' => $status];
            if ($status === 'active') {
                $updates['started_at'] = now()->subDay();
            } elseif ($status === 'closed') {
                $updates['ended_at'] = now();
            } elseif ($status === 'paid') {
                $updates['paid_at'] = now();
            }
            $period->update($updates);

            $showResponse = $this->actingAs($trader)->get("/rekap/{$period->id}");
            $showResponse->assertOk();
            $showResponse->assertSeeText($label);

            if ($status === 'paid') {
                $showResponse->assertSeeText('Tanggal Pelunasan:');
                $showResponse->assertSeeText($period->fresh()->paid_at->translatedFormat('d M Y, H:i'));
            }

            $indexResponse = $this->actingAs($farmer)->get('/rekap');
            $indexResponse->assertOk();
            $indexResponse->assertSeeText($label);
        }
    }

    public function test_billing_panel_displays_farmer_payment_info_and_phone_for_both_roles(): void
    {
        ['farmer' => $farmer, 'trader' => $trader, 'period' => $period] = $this->createSetup([
            'phone' => '081122334455',
            'payment_info' => 'BRI 0099-01-002233-50-1 a.n. Petani Makmur (atau QRIS di tempat)',
        ]);

        foreach ([$trader, $farmer] as $viewer) {
            $response = $this->actingAs($viewer)->get("/rekap/{$period->id}");
            $response->assertOk();
            $response->assertSeeText('081122334455');
            $response->assertSeeText('BRI 0099-01-002233-50-1 a.n. Petani Makmur (atau QRIS di tempat)');
            $response->assertDontSeeText('Informasi pembayaran belum diisi oleh petani');
        }
    }

    public function test_billing_panel_displays_fallback_message_and_farmer_profile_link_when_payment_info_is_empty(): void
    {
        ['farmer' => $farmer, 'trader' => $trader, 'period' => $period] = $this->createSetup([
            'payment_info' => null,
        ]);

        // Pedagang melihat pesan fallback tanpa tautan edit profil petani
        $traderResponse = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderResponse->assertOk();
        $traderResponse->assertSeeText('Informasi pembayaran belum diisi oleh petani');
        $traderResponse->assertDontSeeText('Lengkapi Informasi Pembayaran di Edit Profil');

        // Petani melihat pesan fallback beserta tautan ke halaman Edit Profil
        $farmerResponse = $this->actingAs($farmer)->get("/rekap/{$period->id}");
        $farmerResponse->assertOk();
        $farmerResponse->assertSeeText('Informasi pembayaran belum diisi oleh petani');
        $farmerResponse->assertSeeText('Lengkapi Informasi Pembayaran di Edit Profil');
        $farmerResponse->assertSee(route('profile.edit'));
    }

    public function test_recap_index_displays_consistent_rupiah_two_decimal_format(): void
    {
        ['farmer' => $farmer, 'trader' => $trader] = $this->createSetup([], [
            'status' => 'closed',
            'ended_at' => now(),
            'total_amount' => 987654.30,
        ]);

        foreach ([$trader, $farmer] as $viewer) {
            $response = $this->actingAs($viewer)->get('/rekap');
            $response->assertOk();
            $response->assertSeeText('Rp 987.654,30');
            $response->assertSeeText('Menunggu Pembayaran');
        }
    }
}
