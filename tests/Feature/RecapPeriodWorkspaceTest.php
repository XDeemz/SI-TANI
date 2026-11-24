<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapItem;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapPeriodWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_recap_periods(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Rojolele',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30,
            'offered_price' => 14000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 30,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        $this->get('/rekap')->assertRedirect('/login');
        $this->get("/rekap/{$period->id}")->assertRedirect('/login');
    }

    public function test_involved_farmer_and_trader_can_view_recap_period_list(): void
    {
        $farmer = User::factory()->create(['name' => 'Pak Tani Santoso']);
        $trader = User::factory()->create(['name' => 'Juragan Sayur']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Cabai Rawit Merah Super',
            'unit' => 'kg',
            'initial_daily_capacity' => 80,
            'remaining_daily_capacity' => 40,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40,
            'offered_price' => 35000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 40,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        // Petani melihat daftar rekap
        $farmerResponse = $this->actingAs($farmer)->get('/rekap');
        $farmerResponse->assertOk();
        $farmerResponse->assertSeeText('Cabai Rawit Merah Super');
        $farmerResponse->assertSeeText('Pak Tani Santoso');
        $farmerResponse->assertSeeText('Juragan Sayur');
        $farmerResponse->assertSeeText('40,00 kg/hari');
        $farmerResponse->assertSeeText('Siap Dimulai');

        // Pedagang melihat daftar rekap
        $traderResponse = $this->actingAs($trader)->get('/rekap');
        $traderResponse->assertOk();
        $traderResponse->assertSeeText('Cabai Rawit Merah Super');
        $traderResponse->assertSeeText('Pak Tani Santoso');
        $traderResponse->assertSeeText('Juragan Sayur');
        $traderResponse->assertSeeText('40,00 kg/hari');
        $traderResponse->assertSeeText('Siap Dimulai');
    }

    public function test_involved_farmer_and_trader_can_view_recap_period_detail(): void
    {
        $farmer = User::factory()->create(['name' => 'Petani Bawang', 'phone' => '08123456789']);
        $trader = User::factory()->create(['name' => 'Pedagang Pasar', 'phone' => '08987654321']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Bawang Merah Brebes',
            'unit' => 'kg',
            'initial_daily_capacity' => 150,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(8)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50,
            'offered_price' => 28000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 50,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        // Petani mengakses detail ruang rekap
        $farmerResponse = $this->actingAs($farmer)->get("/rekap/{$period->id}");
        $farmerResponse->assertOk();
        $farmerResponse->assertSeeText('Bawang Merah Brebes');
        $farmerResponse->assertSeeText('Petani Bawang');
        $farmerResponse->assertSeeText('Pedagang Pasar');
        $farmerResponse->assertSeeText('50,00 kg/hari');
        $farmerResponse->assertSeeText('Mode Pantau Petani — Read-Only');

        // Pedagang mengakses detail ruang rekap
        $traderResponse = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $traderResponse->assertOk();
        $traderResponse->assertSeeText('Bawang Merah Brebes');
        $traderResponse->assertSeeText('Petani Bawang');
        $traderResponse->assertSeeText('Pedagang Pasar');
        $traderResponse->assertSeeText('50,00 kg/hari');
        $traderResponse->assertSeeText('Akses Pedagang');
    }

    public function test_third_party_user_cannot_view_recap_period_detail(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();
        $stranger = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Wortel Brastagi',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 70,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30,
            'offered_price' => 10000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 30,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        // Pengguna pihak ketiga (bukan petani dan bukan pedagang terkait) ditolak 403
        $this->actingAs($stranger)->get("/rekap/{$period->id}")->assertForbidden();
    }

    public function test_multi_buyer_isolation_creates_distinct_workspaces_for_each_buyer(): void
    {
        $farmer = User::factory()->create(['name' => 'Pak Haji Komar']);
        $trader1 = User::factory()->create(['name' => 'CV Sumber Makmur']);
        $trader2 = User::factory()->create(['name' => 'PT Berkah Pangan']);

        // Satu komoditas milik petani
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Rojo Lele Delanggu',
            'unit' => 'kg',
            'initial_daily_capacity' => 200,
            'remaining_daily_capacity' => 200,
            'estimated_harvest_date' => now()->addDays(14)->toDateString(),
            'status' => 'open',
        ]);

        // Tawaran Pedagang 1 disetujui (alokasi 80 kg/hari @ Rp 14.500)
        $offer1 = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader1->id,
            'offered_daily_capacity' => 80.00,
            'offered_price' => 14500.00,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period1 = RecapPeriod::create([
            'commodity_offer_id' => $offer1->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader1->id,
            'locked_capacity' => 80.00,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        // Tawaran Pedagang 2 disetujui (alokasi 70 kg/hari @ Rp 15.000)
        $offer2 = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader2->id,
            'offered_daily_capacity' => 70.00,
            'offered_price' => 15000.00,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period2 = RecapPeriod::create([
            'commodity_offer_id' => $offer2->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader2->id,
            'locked_capacity' => 70.00,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        // 1. Petani dapat melihat kedua ruang rekapitulasi terpisah
        $farmerListResponse = $this->actingAs($farmer)->get('/rekap');
        $farmerListResponse->assertOk();
        $farmerListResponse->assertSeeText('CV Sumber Makmur');
        $farmerListResponse->assertSeeText('80,00 kg/hari');
        $farmerListResponse->assertSeeText('PT Berkah Pangan');
        $farmerListResponse->assertSeeText('70,00 kg/hari');

        // Petani dapat membuka detail kedua ruang rekapitulasi
        $this->actingAs($farmer)->get("/rekap/{$period1->id}")->assertOk();
        $this->actingAs($farmer)->get("/rekap/{$period2->id}")->assertOk();

        // 2. Pedagang 1 hanya dapat melihat dan mengakses ruang rekap miliknya
        $trader1ListResponse = $this->actingAs($trader1)->get('/rekap');
        $trader1ListResponse->assertOk();
        $trader1ListResponse->assertSeeText('Beras Rojo Lele Delanggu');
        $trader1ListResponse->assertSeeText('Pak Haji Komar');
        $trader1ListResponse->assertSeeText('CV Sumber Makmur');
        $trader1ListResponse->assertDontSeeText('PT Berkah Pangan');

        // Detail ruang rekap 1 untuk Pedagang 1 memuat identitas lengkap pasangan
        $trader1DetailResponse = $this->actingAs($trader1)->get("/rekap/{$period1->id}");
        $trader1DetailResponse->assertOk();
        $trader1DetailResponse->assertSeeText('Beras Rojo Lele Delanggu');
        $trader1DetailResponse->assertSeeText('Pak Haji Komar');
        $trader1DetailResponse->assertSeeText('CV Sumber Makmur');
        $trader1DetailResponse->assertDontSeeText('PT Berkah Pangan');

        // Pedagang 1 DITOLAK (403) saat mencoba mengakses ruang rekap Pedagang 2
        $this->actingAs($trader1)->get("/rekap/{$period2->id}")->assertForbidden();

        // 3. Pedagang 2 hanya dapat melihat dan mengakses ruang rekap miliknya
        $trader2ListResponse = $this->actingAs($trader2)->get('/rekap');
        $trader2ListResponse->assertOk();
        $trader2ListResponse->assertSeeText('Beras Rojo Lele Delanggu');
        $trader2ListResponse->assertSeeText('Pak Haji Komar');
        $trader2ListResponse->assertSeeText('PT Berkah Pangan');
        $trader2ListResponse->assertDontSeeText('CV Sumber Makmur');

        // Detail ruang rekap 2 untuk Pedagang 2 memuat identitas lengkap pasangan
        $trader2DetailResponse = $this->actingAs($trader2)->get("/rekap/{$period2->id}");
        $trader2DetailResponse->assertOk();
        $trader2DetailResponse->assertSeeText('Beras Rojo Lele Delanggu');
        $trader2DetailResponse->assertSeeText('Pak Haji Komar');
        $trader2DetailResponse->assertSeeText('PT Berkah Pangan');
        $trader2DetailResponse->assertDontSeeText('CV Sumber Makmur');

        // Pedagang 2 DITOLAK (403) saat mencoba mengakses ruang rekap Pedagang 1
        $this->actingAs($trader2)->get("/rekap/{$period1->id}")->assertForbidden();
    }

    public function test_recap_period_detail_displays_chronological_recap_items(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kentang Dieng',
            'unit' => 'kg',
            'initial_daily_capacity' => 200,
            'remaining_daily_capacity' => 150,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50,
            'offered_price' => 15000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 50,
            'status' => 'active',
            'total_amount' => 1250000.00,
        ]);

        RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => '2026-11-20',
            'net_weight' => 45.00,
            'actual_price' => 14000.00,
            'subtotal' => 630000.00,
            'notes' => 'Setoran panen hari ke-1',
        ]);

        RecapItem::create([
            'recap_period_id' => $period->id,
            'transaction_date' => '2026-11-21',
            'net_weight' => 44.00,
            'actual_price' => 14090.91,
            'subtotal' => 620000.00,
            'notes' => 'Setoran panen hari ke-2',
        ]);

        $response = $this->actingAs($trader)->get("/rekap/{$period->id}");
        $response->assertOk();
        $response->assertSeeText('Setoran panen hari ke-1');
        $response->assertSeeText('Setoran panen hari ke-2');
        $response->assertSeeText('630.000,00');
        $response->assertSeeText('620.000,00');
    }

    public function test_approved_offers_display_recap_link_in_farmer_and_trader_views(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Jagung Manis',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(7)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50,
            'offered_price' => 6000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $period = RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 50,
            'status' => 'ready',
            'total_amount' => 0.00,
        ]);

        // Halaman tawaran masuk petani memuat tautan Buka Ruang Rekap
        $farmerOffersResponse = $this->actingAs($farmer)->get('/farmer/offers');
        $farmerOffersResponse->assertOk();
        $farmerOffersResponse->assertSeeText('Buka Ruang Rekap');
        $farmerOffersResponse->assertSee("/rekap/{$period->id}");

        // Halaman tawaran saya pedagang memuat tautan Buka Ruang Rekap
        $traderOffersResponse = $this->actingAs($trader)->get('/trader/offers');
        $traderOffersResponse->assertOk();
        $traderOffersResponse->assertSeeText('Buka Ruang Rekap');
        $traderOffersResponse->assertSee("/rekap/{$period->id}");
    }
}
