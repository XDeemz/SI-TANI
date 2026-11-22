<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerCommodityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_farmer_commodities(): void
    {
        $commodity = Commodity::create([
            'farmer_id' => User::factory()->create()->id,
            'name' => 'Beras Pandan Wangi',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $this->get('/farmer/commodities')->assertRedirect('/login');
        $this->get('/farmer/commodities/create')->assertRedirect('/login');
        $this->post('/farmer/commodities', [])->assertRedirect('/login');
        $this->get("/farmer/commodities/{$commodity->id}/edit")->assertRedirect('/login');
        $this->put("/farmer/commodities/{$commodity->id}", [])->assertRedirect('/login');
        $this->patch("/farmer/commodities/{$commodity->id}/toggle-status")->assertRedirect('/login');
        $this->delete("/farmer/commodities/{$commodity->id}")->assertRedirect('/login');
    }

    public function test_farmer_can_view_commodities_list_and_only_sees_their_own(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();

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
            'name' => 'Bawang Merah Petani 2',
            'unit' => 'kg',
            'initial_daily_capacity' => 80,
            'remaining_daily_capacity' => 80,
            'estimated_harvest_date' => now()->addDays(7)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($farmer1)->get('/farmer/commodities');

        $response->assertOk();
        $response->assertSeeText('Cabai Rawit Merah Petani 1');
        $response->assertDontSeeText('Bawang Merah Petani 2');
    }

    public function test_farmer_can_view_create_commodity_page(): void
    {
        $farmer = User::factory()->create();

        $response = $this->actingAs($farmer)->get('/farmer/commodities/create');

        $response->assertOk();
        $response->assertSeeText('Tambah Komoditas Baru');
    }

    public function test_farmer_can_create_commodity_with_automatic_remaining_capacity(): void
    {
        $farmer = User::factory()->create();

        $response = $this->actingAs($farmer)->post('/farmer/commodities', [
            'name' => 'Jagung Manis Madu',
            'unit' => 'kg',
            'initial_daily_capacity' => 200.50,
            'estimated_harvest_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Panen segar petik langsung dari kebun.',
            'status' => 'open',
        ]);

        $response->assertRedirect('/farmer/commodities');
        $response->assertSessionHas('success', 'Komoditas berhasil ditambahkan.');

        $this->assertDatabaseHas('commodities', [
            'farmer_id' => $farmer->id,
            'name' => 'Jagung Manis Madu',
            'unit' => 'kg',
            'initial_daily_capacity' => 200.50,
            'remaining_daily_capacity' => 200.50, // Otomatis sama dengan initial_daily_capacity
            'status' => 'open',
        ]);
    }

    public function test_create_commodity_validation_rules(): void
    {
        $farmer = User::factory()->create();

        $response = $this->actingAs($farmer)->post('/farmer/commodities', [
            'name' => '',
            'unit' => '',
            'initial_daily_capacity' => -10, // Harus gt:0
            'estimated_harvest_date' => 'bukan-tanggal',
        ]);

        $response->assertSessionHasErrors([
            'name',
            'unit',
            'initial_daily_capacity',
            'estimated_harvest_date',
        ]);
    }

    public function test_farmer_can_view_edit_commodity_page(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Tomat Ceri Hidroponik',
            'unit' => 'kg',
            'initial_daily_capacity' => 30,
            'remaining_daily_capacity' => 30,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($farmer)->get("/farmer/commodities/{$commodity->id}/edit");

        $response->assertOk();
        $response->assertSeeText('Tomat Ceri Hidroponik');
    }

    public function test_user_cannot_view_or_edit_other_farmers_commodity(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer1->id,
            'name' => 'Komoditas Petani 1',
            'unit' => 'kg',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // Farmer 2 mencoba akses show (redirects) atau edit -> 403 Forbidden
        $this->actingAs($farmer2)->get("/farmer/commodities/{$commodity->id}")->assertForbidden();
        $this->actingAs($farmer2)->get("/farmer/commodities/{$commodity->id}/edit")->assertForbidden();
    }

    public function test_user_cannot_update_other_farmers_commodity(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer1->id,
            'name' => 'Komoditas Petani 1',
            'unit' => 'kg',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($farmer2)->put("/farmer/commodities/{$commodity->id}", [
            'name' => 'Dibajak Pengguna Lain',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('commodities', [
            'id' => $commodity->id,
            'name' => 'Komoditas Petani 1',
        ]);
    }

    public function test_farmer_can_update_commodity_and_adjust_capacity(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Organik',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 100.00,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // Tambah kapasitas awal dari 100 menjadi 150 (selisih +50)
        $response = $this->actingAs($farmer)->put("/farmer/commodities/{$commodity->id}", [
            'name' => 'Beras Organik Super',
            'unit' => 'kg',
            'initial_daily_capacity' => 150.00,
            'estimated_harvest_date' => now()->addDays(12)->toDateString(),
            'notes' => 'Sudah bersertifikasi organik.',
            'status' => 'open',
        ]);

        $response->assertRedirect('/farmer/commodities');
        $response->assertSessionHas('success', 'Komoditas berhasil diperbarui.');

        $commodity->refresh();
        $this->assertSame('Beras Organik Super', $commodity->name);
        $this->assertEquals('150.00', $commodity->initial_daily_capacity);
        $this->assertEquals('150.00', $commodity->remaining_daily_capacity);
    }

    public function test_farmer_can_adjust_capacity_with_partially_locked_capacity(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Merah',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 60.00, // 40.00 terkunci
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // Petani menaikkan kapasitas awal ke 120 (selisih +20, sisa kapasitas harus menjadi 60 + 20 = 80)
        $response = $this->actingAs($farmer)->put("/farmer/commodities/{$commodity->id}", [
            'name' => 'Beras Merah',
            'unit' => 'kg',
            'initial_daily_capacity' => 120.00,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response->assertRedirect('/farmer/commodities');

        $commodity->refresh();
        $this->assertEquals('120.00', $commodity->initial_daily_capacity);
        $this->assertEquals('80.00', $commodity->remaining_daily_capacity);
    }

    public function test_farmer_cannot_reduce_initial_capacity_below_locked_capacity(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Merah',
            'unit' => 'kg',
            'initial_daily_capacity' => 100.00,
            'remaining_daily_capacity' => 60.00, // 40.00 terkunci
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // Petani mencoba menurunkan kapasitas awal ke 30 (lebih kecil dari 40 yang terkunci)
        $response = $this->actingAs($farmer)->put("/farmer/commodities/{$commodity->id}", [
            'name' => 'Beras Merah',
            'unit' => 'kg',
            'initial_daily_capacity' => 30.00,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        $response->assertSessionHasErrors('initial_daily_capacity');

        $commodity->refresh();
        $this->assertEquals('100.00', $commodity->initial_daily_capacity);
        $this->assertEquals('60.00', $commodity->remaining_daily_capacity);
    }

    public function test_farmer_can_toggle_commodity_status(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kentang Dieng',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(8)->toDateString(),
            'status' => 'open',
        ]);

        // Toggle 1: open -> closed
        $response = $this->actingAs($farmer)->patch("/farmer/commodities/{$commodity->id}/toggle-status");
        $response->assertRedirect();
        $commodity->refresh();
        $this->assertSame('closed', $commodity->status);

        // Toggle 2: closed -> open
        $response2 = $this->actingAs($farmer)->patch("/farmer/commodities/{$commodity->id}/toggle-status");
        $response2->assertRedirect();
        $commodity->refresh();
        $this->assertSame('open', $commodity->status);
    }

    public function test_user_cannot_toggle_status_of_other_farmers_commodity(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer1->id,
            'name' => 'Kentang Dieng',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(8)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($farmer2)->patch("/farmer/commodities/{$commodity->id}/toggle-status");
        $response->assertForbidden();

        $commodity->refresh();
        $this->assertSame('open', $commodity->status);
    }

    public function test_farmer_can_delete_commodity_without_active_contracts(): void
    {
        $farmer = User::factory()->create();
        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kangkung Hidroponik',
            'unit' => 'ikat',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($farmer)->delete("/farmer/commodities/{$commodity->id}");

        $response->assertRedirect('/farmer/commodities');
        $response->assertSessionHas('success', 'Komoditas berhasil dihapus.');

        $this->assertDatabaseMissing('commodities', ['id' => $commodity->id]);
    }

    public function test_user_cannot_delete_other_farmers_commodity(): void
    {
        $farmer1 = User::factory()->create();
        $farmer2 = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer1->id,
            'name' => 'Kangkung Hidroponik',
            'unit' => 'ikat',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($farmer2)->delete("/farmer/commodities/{$commodity->id}");
        $response->assertForbidden();

        $this->assertDatabaseHas('commodities', ['id' => $commodity->id]);
    }

    public function test_farmer_cannot_delete_commodity_with_approved_offers(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Beras Rojo Lele',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 60,
            'estimated_harvest_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);

        // Ada tawaran yang sudah disetujui (approved)
        CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $trader->id,
            'offered_daily_capacity' => 40,
            'offered_price' => 14000,
            'status' => 'approved',
            'responded_at' => now(),
        ]);

        $response = $this->actingAs($farmer)->delete("/farmer/commodities/{$commodity->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('commodities', ['id' => $commodity->id]);
    }

    public function test_farmer_cannot_delete_commodity_with_active_recap_period(): void
    {
        $farmer = User::factory()->create();
        $trader = User::factory()->create();

        $commodity = Commodity::create([
            'farmer_id' => $farmer->id,
            'name' => 'Kopi Robusta',
            'unit' => 'kg',
            'initial_daily_capacity' => 200,
            'remaining_daily_capacity' => 150,
            'estimated_harvest_date' => now()->addDays(15)->toDateString(),
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

        RecapPeriod::create([
            'commodity_offer_id' => $offer->id,
            'commodity_id' => $commodity->id,
            'farmer_id' => $farmer->id,
            'trader_id' => $trader->id,
            'locked_capacity' => 50,
            'status' => 'active',
            'started_at' => now(),
            'total_amount' => 0,
        ]);

        $response = $this->actingAs($farmer)->delete("/farmer/commodities/{$commodity->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('commodities', ['id' => $commodity->id]);
    }
}
