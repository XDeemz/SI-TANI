<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContextualDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard_and_see_both_contextual_panels(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'payment_info' => 'BCA 123456789 a/n Petani Sukses',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard');

        // Memastikan kedua konteks peran ditampilkan secara berdampingan tanpa penguncian statis
        $response->assertSeeText('Mode Sebagai Petani');
        $response->assertSeeText('Komoditas Saya');
        $response->assertSeeText('Tawaran Masuk');

        $response->assertSeeText('Mode Sebagai Pedagang');
        $response->assertSeeText('Cari Komoditas');
        $response->assertSeeText('Tawaran Saya');
        $response->assertSeeText('Rekap Aktif');
    }

    public function test_profile_reminder_is_displayed_when_phone_or_payment_info_is_missing(): void
    {
        $incompleteUser = User::factory()->create([
            'phone' => null,
            'payment_info' => null,
        ]);

        $response = $this->actingAs($incompleteUser)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('Informasi Profil Belum Lengkap');
        $response->assertSeeText('Lengkapi Profil');
    }

    public function test_profile_reminder_is_hidden_when_profile_is_fully_completed(): void
    {
        $completeUser = User::factory()->create([
            'phone' => '081234567890',
            'payment_info' => 'Mandiri 123456789',
        ]);

        $response = $this->actingAs($completeUser)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSeeText('Informasi Profil Belum Lengkap');
    }

    public function test_trader_available_commodities_stat_excludes_own_commodities(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Komoditas milik user sendiri (tidak boleh dihitung di Cari Komoditas untuk Pedagang)
        Commodity::create([
            'farmer_id' => $user->id,
            'name' => 'Beras Pandan Wangi Milik Sendiri',
            'unit' => 'kg',
            'initial_daily_capacity' => 100,
            'remaining_daily_capacity' => 100,
            'estimated_harvest_date' => now()->addDays(7),
            'status' => 'open',
        ]);

        // Komoditas milik petani lain (boleh dihitung di Cari Komoditas)
        Commodity::create([
            'farmer_id' => $otherUser->id,
            'name' => 'Cabai Rawit Merah Petani Lain',
            'unit' => 'kg',
            'initial_daily_capacity' => 50,
            'remaining_daily_capacity' => 50,
            'estimated_harvest_date' => now()->addDays(5),
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('traderStats', function ($stats) {
            return $stats['available_commodities_count'] === 1;
        });
        $response->assertViewHas('farmerStats', function ($stats) {
            return $stats['my_commodities_count'] === 1;
        });
    }
}
