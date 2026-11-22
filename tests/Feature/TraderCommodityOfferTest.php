<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraderCommodityOfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_trader_features(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Pandan Wangi',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $this->get('/trader/commodities')->assertRedirect('/login');
        $this->get("/trader/commodities/{$commodity->id}")->assertRedirect('/login');
        $this->post("/trader/commodities/{$commodity->id}/offers", [])->assertRedirect('/login');
        $this->get('/trader/offers')->assertRedirect('/login');
    }

    public function test_catalog_only_shows_open_commodities_from_other_farmers(): void
    {
        $trader = User::factory()->create();
        $otherFarmer = User::factory()->create();

        // 1. Komoditas milik trader sendiri (open) -> TIDAK boleh muncul di katalog trader
        $ownCommodity = Commodity::create([
            'farmer_id' => $trader->id,
            'name' => 'Komoditas Milik Sendiri',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // 2. Komoditas milik petani lain (open) -> HARUS muncul
        $otherOpenCommodity = Commodity::create([
            'farmer_id' => $otherFarmer->id,
            'name' => 'Bawang Brebes Petani Lain',
            'unit' => 'kg',
            'initial_daily_capacity' => 80,
            'remaining_daily_capacity' => 80,
            'estimated_harvest_date' => now()->addDays(7)->toDateString(),
            'status' => 'open',
        ]);

        // 3. Komoditas milik petani lain (closed) -> TIDAK boleh muncul
        $otherClosedCommodity = Commodity::create([
            'farmer_id' => $otherFarmer->id,
            'name' => 'Cabai Rawit Tutup',
            'unit' => 'kg',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'closed',
        ]);

        $response = $this->actingAs($trader)->get('/trader/commodities');

        $response->assertOk();
        $response->assertSeeText('Bawang Brebes Petani Lain');
        $response->assertDontSeeText('Komoditas Milik Sendiri');
        $response->assertDontSeeText('Cabai Rawit Tutup');
    }

    public function test_trader_can_search_commodities_by_name_or_notes(): void
    {
        $trader = User::factory()->create();
        $farmer = User::factory()->create();

        Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Merah Cianjur',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'notes' => 'Padi varietas unggulan Cianjur.',
            'status' => 'open',
        ]);

        Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kentang Dieng Super',
            'unit' => 'kg',
            'initial_daily_capacity' => 200,
            'remaining_daily_capacity' => 200,
            'estimated_harvest_date' => now()->addDays(15)->toDateString(),
            'notes' => 'Sortiran grade A Wonosobo.',
            'status' => 'open',
        ]);

        // Cari berdasarkan nama
        $searchNameResponse = $this->actingAs($trader)->get('/trader/commodities?search=Cianjur');
        $searchNameResponse->assertSeeText('Beras Merah Cianjur');
        $searchNameResponse->assertDontSeeText('Kentang Dieng Super');

        // Cari berdasarkan catatan
        $searchNotesResponse = $this->actingAs($trader)->get('/trader/commodities?search=Wonosobo');
        $searchNotesResponse->assertSeeText('Kentang Dieng Super');
        $searchNotesResponse->assertDontSeeText('Beras Merah Cianjur');
    }

    public function test_trader_can_filter_commodities_with_available_capacity(): void
    {
        $trader = User::factory()->create();
        $farmer = User::factory()->create();

        Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Jagung Pipil Tersedia',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 50, // Tersedia
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kedelai Habis Kapasitas',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 0, // Habis
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($trader)->get('/trader/commodities?available_only=1');

        $response->assertOk();
        $response->assertSeeText('Jagung Pipil Tersedia');
        $response->assertDontSeeText('Kedelai Habis Kapasitas');
    }

    public function test_anti_self_offer_cannot_view_or_offer_own_commodity(): void
    {
        $user = User::factory()->create();

        $ownCommodity = Commodity::create([
            'farmer_id' => $user->id,
            'name' => 'Padi Milik Sendiri',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // Coba lihat detail / form penawaran komoditas sendiri -> 403 Forbidden
        $this->actingAs($user)
            ->get("/trader/commodities/{$ownCommodity->id}")
            ->assertForbidden();

        // Coba kirim POST penawaran ke komoditas sendiri -> 403 Forbidden
        $this->actingAs($user)
            ->post("/trader/commodities/{$ownCommodity->id}/offers", [
                'offered_daily_capacity' => 20,
                'offered_price' => 12000,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('commodity_offers', [
            'commodity_id' => $ownCommodity->id,
            'trader_id' => $user->id,
        ]);
    }

    public function test_trader_can_view_commodity_detail_and_submit_valid_offer(): void
    {
        $farmer = User::factory()->create(['name' => 'Pak Tani Sukses', 'phone' => '08123456789']);
        $trader = User::factory()->create(['name' => 'Pedagang Makmur']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Wortel Brastagi',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(12)->toDateString(),
            'status' => 'open',
        ]);

        // 1. Tampilkan detail komoditas
        $detailResponse = $this->actingAs($trader)->get("/trader/commodities/{$commodity->id}");
        $detailResponse->assertOk();
        $detailResponse->assertSeeText('Wortel Brastagi');
        $detailResponse->assertSeeText('Pak Tani Sukses');

        // 2. Kirim penawaran valid
        $offerResponse = $this->actingAs($trader)->post("/trader/commodities/{$commodity->id}/offers", [
            'offered_daily_capacity' => 40.00,
            'offered_price' => 12500.00,
        ]);

        $offerResponse->assertRedirect('/trader/offers');
        $offerResponse->assertSessionHas('success');

        // Pastikan tersimpan dengan status 'pending'
        $this->assertDatabaseHas('commodity_offers', [
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40.00,
            'offered_price' => 12500.00,
            'status' => 'pending',
        ]);

        // Sisa kapasitas komoditas BELUM berkurang (sampai disetujui petani pada Siklus 2.3)
        $commodity->refresh();
        $this->assertEquals('100.00', $commodity->remaining_daily_capacity);
    }

    public function test_trader_cannot_submit_offer_exceeding_remaining_daily_capacity(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kacang Tanah',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 50, // Sisa hanya 50
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($trader)->post("/trader/commodities/{$commodity->id}/offers", [
            'offered_daily_capacity' => 60.00, // Melebihi 50
            'offered_price' => 20000,
        ]);

        $response->assertSessionHasErrors('offered_daily_capacity');
        $this->assertDatabaseMissing('commodity_offers', [
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
        ]);
    }

    public function test_trader_cannot_submit_offer_with_zero_or_negative_values(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kacang Panjang',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($trader)->post("/trader/commodities/{$commodity->id}/offers", [
            'offered_daily_capacity' => 0,
            'offered_price' => -500,
        ]);

        $response->assertSessionHasErrors([
            'offered_daily_capacity',
            'offered_price',
        ]);
    }

    public function test_trader_cannot_submit_offer_to_closed_commodity(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Buncis Ditutup',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'closed',
        ]);

        $response = $this->actingAs($trader)->post("/trader/commodities/{$commodity->id}/offers", [
            'offered_daily_capacity' => 25,
            'offered_price' => 10000,
        ]);

        $response->assertForbidden();
    }

    public function test_trader_cannot_submit_duplicate_active_offer_for_same_commodity(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Singkong Manis',
            'unit' => 'kg',
            'initial_daily_capacity' => 200,
            'remaining_daily_capacity' => 200,
            'estimated_harvest_date' => now()->addDays(15)->toDateString(),
            'status' => 'open',
        ]);

        // Tawaran pertama dibuat
        CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50,
            'offered_price' => 5000,
            'status' => 'pending',
        ]);

        // Tawaran kedua ke komoditas yang sama saat tawaran pertama masih pending
        $response = $this->actingAs($trader)->post("/trader/commodities/{$commodity->id}/offers", [
            'offered_daily_capacity' => 30,
            'offered_price' => 6000,
        ]);

        $response->assertSessionHasErrors('offered_daily_capacity');

        // Pastikan hanya ada 1 tawaran di database
        $this->assertEquals(1, CommodityOffer::where('commodity_id', $commodity->id)->where('trader_id', $trader->id)->count());
    }

    public function test_trader_can_view_their_own_submitted_offers(): void
    {
        $trader1 = User::factory()->create();
        $trader2 = User::factory()->create();
        $farmer = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Bayam Hijau',
            'unit' => 'ikat',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $offer1 = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader1->id,
            'offered_daily_capacity' => 20,
            'offered_price' => 3000,
            'status' => 'pending',
        ]);

        $offer2 = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader2->id,
            'offered_daily_capacity' => 40,
            'offered_price' => 3500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($trader1)->get('/trader/offers');

        $response->assertOk();
        $response->assertSeeText('20,00');
        $response->assertDontSeeText('40,00');
    }

    public function test_trader_can_cancel_pending_offer(): void
    {
        $trader = User::factory()->create();
        $farmer = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Selada Keriting',
            'unit' => 'kg',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 15,
            'offered_price' => 18000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($trader)->delete("/trader/offers/{$offer->id}");

        $response->assertRedirect('/trader/offers');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('commodity_offers', ['id' => $offer->id]);
    }

    public function test_trader_cannot_cancel_approved_or_rejected_offer(): void
    {
        $trader = User::factory()->create();
        $farmer = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Melon Madu',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $approvedOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30,
            'offered_price' => 25000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        // Coba batalkan tawaran yang sudah disetujui -> Policy menolak (403 Forbidden)
        $response = $this->actingAs($trader)->delete("/trader/offers/{$approvedOffer->id}");
        $response->assertForbidden();

        $this->assertDatabaseHas('commodity_offers', ['id' => $approvedOffer->id]);
    }

    public function test_user_cannot_cancel_other_traders_offer(): void
    {
        $trader1 = User::factory()->create();
        $trader2 = User::factory()->create();
        $farmer = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Semangka Merah',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(8)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader1->id,
            'offered_daily_capacity' => 25,
            'offered_price' => 8000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($trader2)->delete("/trader/offers/{$offer->id}");
        $response->assertForbidden();

        $this->assertDatabaseHas('commodity_offers', ['id' => $offer->id]);
    }
}
