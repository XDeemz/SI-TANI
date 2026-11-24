<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerOfferManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_farmer_offer_management(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Pandan Wangi',
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
            'status' => 'pending',
        ]);

        $this->get('/farmer/offers')->assertRedirect('/login');
        $this->patch("/farmer/offers/{$offer->id}/accept")->assertRedirect('/login');
        $this->patch("/farmer/offers/{$offer->id}/reject")->assertRedirect('/login');
    }

    public function test_farmer_can_view_incoming_offers_for_their_commodities(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();
        $trader = User::factory()->create();

        $commodity1 = Commodity::create([
            'farmer_id' => $farmer1->id,
            'name' => 'Cabai Rawit Merah Petani 1',
            'unit' => 'kg',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $commodity2 = Commodity::create([
            'farmer_id' => $farmer2->id,
            'name' => 'Bawang Brebes Petani 2',
            'unit' => 'kg',
            'initial_daily_capacity' => 80,
            'remaining_daily_capacity' => 80,
            'estimated_harvest_date' => now()->addDays(7)->toDateString(),
            'status' => 'open',
        ]);

        $offer1 = CommodityOffer::create([
            'commodity_id' => $commodity1->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 25,
            'offered_price' => 30000,
            'status' => 'pending',
        ]);

        $offer2 = CommodityOffer::create([
            'commodity_id' => $commodity2->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40,
            'offered_price' => 25000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($farmer1)->get('/farmer/offers');

        $response->assertOk();
        $response->assertSeeText('Cabai Rawit Merah Petani 1');
        $response->assertDontSeeText('Bawang Brebes Petani 2');
    }

    public function test_farmer_can_filter_incoming_offers_by_status(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Cianjur',
            'unit' => 'kg',
            'initial_daily_capacity' => 200,
            'remaining_daily_capacity' => 200,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $pendingOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50,
            'offered_price' => 13000,
            'status' => 'pending',
        ]);

        $rejectedOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40,
            'offered_price' => 11000,
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        $response = $this->actingAs($farmer)->get('/farmer/offers?status=pending');

        $response->assertOk();
        $response->assertSeeText('13.000,00');
        $response->assertDontSeeText('11.000,00');
    }

    public function test_other_user_cannot_accept_or_reject_offer_for_another_farmers_commodity(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer1->id,
            'name' => 'Wortel Super',
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
            'offered_price' => 8000,
            'status' => 'pending',
        ]);

        // Petani 2 mencoba menyetujui atau menolak -> 403 Forbidden
        $this->actingAs($farmer2)->patch("/farmer/offers/{$offer->id}/accept")->assertForbidden();
        $this->actingAs($farmer2)->patch("/farmer/offers/{$offer->id}/reject")->assertForbidden();

        // Pedagang pengaju tawaran mencoba menyetujui -> 403 Forbidden
        $this->actingAs($trader)->patch("/farmer/offers/{$offer->id}/accept")->assertForbidden();
        $this->actingAs($trader)->patch("/farmer/offers/{$offer->id}/reject")->assertForbidden();

        // Pastikan status tawaran tidak berubah
        $offer->refresh();
        $this->assertSame('pending', $offer->status);
    }

    public function test_farmer_can_accept_offer_reducing_commodity_capacity_and_creating_recap_period(): void
    {
        $farmer = User::factory()->create(['name' => 'Pak Tani']);
        $trader = User::factory()->create(['name' => 'Juragan Sayur']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Tomat Ceri Hidroponik',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 100.00,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40.00,
            'offered_price' => 20000.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($farmer)->patch("/farmer/offers/{$offer->id}/accept");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status tawaran berubah menjadi approved
        $offer->refresh();
        $this->assertSame('approved', $offer->status);
        $this->assertNotNull($offer->responded_at);

        // Sisa kapasitas komoditas berkurang: 100.00 - 40.00 = 60.00
        $commodity->refresh();
        $this->assertEquals('60.00', $commodity->remaining_daily_capacity);

        // Rekap periode otomatis terbentuk dengan status ready dan locked_capacity = 40.00
        $this->assertDatabaseHas('recap_periods', [
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => '40.00',
            'status' => 'ready',
            'total_amount' => '0.00',
        ]);
    }

    public function test_accept_offer_fails_if_remaining_daily_capacity_is_insufficient(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Melon Golden',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 20.00, // Sisa hanya 20.00
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50.00, // Meminta 50.00
            'offered_price' => 18000.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($farmer)->patch("/farmer/offers/{$offer->id}/accept");

        $response->assertSessionHas('error');

        // Status tawaran tetap pending
        $offer->refresh();
        $this->assertSame('pending', $offer->status);

        // Sisa kapasitas komoditas tetap tidak berubah
        $commodity->refresh();
        $this->assertEquals('20.00', $commodity->remaining_daily_capacity);

        // Tidak ada record RecapPeriod yang dibuat
        $this->assertDatabaseMissing('recap_periods', [
            'commodity_offer_id' => $offer->id,
        ]);
    }

    public function test_accept_offer_fails_if_commodity_status_is_closed(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kentang Granola',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 100.00,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'closed', // Status closed
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30.00,
            'offered_price' => 15000.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($farmer)->patch("/farmer/offers/{$offer->id}/accept");

        $response->assertSessionHas('error');

        $offer->refresh();
        $this->assertSame('pending', $offer->status);

        $this->assertDatabaseMissing('recap_periods', [
            'commodity_offer_id' => $offer->id,
        ]);
    }

    public function test_farmer_can_reject_offer_without_affecting_capacity_or_creating_recap_period(): void
    {
        $farmer = User::factory()->create(['name' => 'Petani Jagung']);
        $trader = User::factory()->create(['name' => 'Pedagang Pakan']);

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Jagung Hibrida',
            'unit' => 'kg',
            'initial_daily_capacity' => 200.00,
            'remaining_daily_capacity' => 200.00,
            'estimated_harvest_date' => now()->addDays(14)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 50.00,
            'offered_price' => 4500.00, // Harga dinilai terlalu rendah
            'status' => 'pending',
        ]);

        $response = $this->actingAs($farmer)->patch("/farmer/offers/{$offer->id}/reject", [
            'farmer_notes' => 'Harga penawaran terlalu rendah di bawah estimasi modal panen.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status berubah menjadi rejected dan alasan tersimpan
        $offer->refresh();
        $this->assertSame('rejected', $offer->status);
        $this->assertSame('Harga penawaran terlalu rendah di bawah estimasi modal panen.', $offer->farmer_notes);
        $this->assertNotNull($offer->responded_at);

        // Kapasitas komoditas tidak berkurang
        $commodity->refresh();
        $this->assertEquals('200.00', $commodity->remaining_daily_capacity);

        // Tidak ada RecapPeriod
        $this->assertDatabaseMissing('recap_periods', [
            'commodity_offer_id' => $offer->id,
        ]);
    }

    public function test_farmer_can_accept_offer_with_farmer_notes_and_stored_in_database(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kacang Kedelai Unggul',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 100.00,
            'estimated_harvest_date' => now()->addDays(7)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30.00,
            'offered_price' => 15000.00,
            'status' => 'pending',
        ]);

        $notes = 'Pengiriman perdana dapat dimulai hari Senin pagi.';
        $response = $this->actingAs($farmer)->patch("/farmer/offers/{$offer->id}/accept", [
            'farmer_notes' => $notes,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $offer->refresh();
        $this->assertSame('approved', $offer->status);
        $this->assertSame($notes, $offer->farmer_notes);
        $this->assertDatabaseHas('commodity_offers', [
            'id' => $offer->id,
            'status' => 'approved',
            'farmer_notes' => $notes,
        ]);
    }

    public function test_farmer_fails_to_reject_offer_if_farmer_notes_is_empty(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Bawang Daun',
            'unit' => 'kg',
            'initial_daily_capacity' => 50.00,
            'remaining_daily_capacity' => 50.00,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $offer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 15.00,
            'offered_price' => 12000.00,
            'status' => 'pending',
        ]);

        // Coba tolak tanpa menyertakan farmer_notes
        $response = $this->actingAs($farmer)->patch("/farmer/offers/{$offer->id}/reject", [
            'farmer_notes' => '',
        ]);

        $response->assertSessionHasErrors('farmer_notes');

        $offer->refresh();
        $this->assertSame('pending', $offer->status);
        $this->assertNull($offer->farmer_notes);
    }

    public function test_already_processed_offer_cannot_be_processed_again(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kopi Robusta',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 100.00,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $approvedOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30.00,
            'offered_price' => 25000.00,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        // Coba setujui kembali tawaran yang sudah approved -> 403 Forbidden
        $this->actingAs($farmer)->patch("/farmer/offers/{$approvedOffer->id}/accept")->assertForbidden();
        // Coba tolak tawaran yang sudah approved -> 403 Forbidden
        $this->actingAs($farmer)->patch("/farmer/offers/{$approvedOffer->id}/reject")->assertForbidden();

        $rejectedOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 30.00,
            'offered_price' => 20000.00,
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        // Coba setujui tawaran yang sudah rejected -> 403 Forbidden
        $this->actingAs($farmer)->patch("/farmer/offers/{$rejectedOffer->id}/accept")->assertForbidden();
        // Coba tolak kembali tawaran yang sudah rejected -> 403 Forbidden
        $this->actingAs($farmer)->patch("/farmer/offers/{$rejectedOffer->id}/reject")->assertForbidden();
    }

    public function test_farmer_can_view_farmer_notes_on_processed_offers(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Wortel Organik',
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
            'offered_price' => 15000,
            'status' => 'approved',
            'farmer_notes' => 'Catatan: Pasokan siap dikirim tiap subuh.',
            'responded_at' => now(),
        ]);

        $rejectedOffer = CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 20,
            'offered_price' => 10000,
            'status' => 'rejected',
            'farmer_notes' => 'Alasan: Penawaran harga di bawah estimasi modal panen.',
            'responded_at' => now(),
        ]);

        $response = $this->actingAs($farmer)->get('/farmer/offers');

        $response->assertOk();
        $response->assertSeeText('Catatan: Pasokan siap dikirim tiap subuh.');
        $response->assertSeeText('Catatan Petani:');
        $response->assertSeeText('Alasan: Penawaran harga di bawah estimasi modal panen.');
        $response->assertSeeText('Alasan Penolakan Petani:');
    }
}
