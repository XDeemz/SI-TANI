<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard kontekstual SI-TANI.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Status kelengkapan profil (phone dan payment_info)
        $profileIncomplete = empty($user->phone) || empty($user->payment_info);

        // Ringkasan aktivitas kontekstual Petani
        $farmerStats = [
            'my_commodities_count' => $user->commodities()->count(),
            'pending_offers_count' => CommodityOffer::whereIn('commodity_id', $user->commodities()->select('id'))
                ->where('status', 'pending')
                ->count(),
        ];

        // Ringkasan aktivitas kontekstual Pedagang (komoditas milik pengguna lain)
        $traderStats = [
            'available_commodities_count' => Commodity::where('status', 'open')
                ->where('farmer_id', '!=', $user->id)
                ->count(),
            'my_offers_count' => $user->offers()->count(),
            'active_recaps_count' => $user->traderRecapPeriods()
                ->where('status', 'active')
                ->count(),
        ];

        return view('dashboard', [
            'user' => $user,
            'profileIncomplete' => $profileIncomplete,
            'farmerStats' => $farmerStats,
            'traderStats' => $traderStats,
        ]);
    }
}
