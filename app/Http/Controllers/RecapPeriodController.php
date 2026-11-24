<?php

namespace App\Http\Controllers;

use App\Models\RecapPeriod;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecapPeriodController extends Controller
{
    use AuthorizesRequests;

    /**
     * Tampilkan daftar ruang rekapitulasi terpisah berdasarkan peran kontekstual (Pedagang vs Petani).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Periode rekap di mana pengguna berperan sebagai Pedagang
        $traderRecaps = RecapPeriod::with(['commodity', 'commodityOffer', 'farmer', 'trader'])
            ->where('trader_id', $user->id)
            ->latest('updated_at')
            ->get();

        // Periode rekap di mana pengguna berperan sebagai Petani
        $farmerRecaps = RecapPeriod::with(['commodity', 'commodityOffer', 'farmer', 'trader'])
            ->where('farmer_id', $user->id)
            ->latest('updated_at')
            ->get();

        return view('recap-periods.index', compact('traderRecaps', 'farmerRecaps'));
    }

    /**
     * Tampilkan detail ruang rekap dan riwayat baris sheet per pasangan kontrak terisolasi.
     */
    public function show(Request $request, RecapPeriod $recapPeriod): View
    {
        $this->authorize('view', $recapPeriod);

        $recapPeriod->load([
            'commodity',
            'commodityOffer',
            'farmer',
            'trader',
            'recapItems' => function ($query) {
                $query->orderBy('transaction_date', 'asc');
            },
        ]);

        $user = $request->user();
        $isTrader = (int) $user->id === (int) $recapPeriod->trader_id;
        $isFarmer = (int) $user->id === (int) $recapPeriod->farmer_id;

        return view('recap-periods.show', compact('recapPeriod', 'isTrader', 'isFarmer'));
    }
}
