<?php

namespace App\Http\Controllers\Trader;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\CommodityOffer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommodityCatalogController extends Controller
{
    /**
     * Tampilkan katalog komoditas terbuka milik petani lain yang dapat ditawar oleh pedagang.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Commodity::with('farmer')
            ->where('status', 'open')
            ->where('farmer_id', '!=', $user->id);

        // Filter pencarian berdasarkan nama atau catatan
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter hanya yang masih memiliki sisa kapasitas > 0
        if ($request->boolean('available_only')) {
            $query->where('remaining_daily_capacity', '>', 0);
        }

        $commodities = $query->latest()->paginate(9)->withQueryString();

        return view('trader.commodities.index', compact('commodities'));
    }

    /**
     * Tampilkan detail komoditas dan formulir pengajuan tawaran.
     */
    public function show(Request $request, Commodity $commodity): View
    {
        // Anti-Self-Offer: dilarang mengakses/menawar komoditas sendiri
        if ((int) $commodity->farmer_id === (int) $request->user()->id) {
            abort(403, 'Anda tidak dapat menawar komoditas milik Anda sendiri.');
        }

        // Cek apakah pedagang sudah memiliki tawaran aktif pada komoditas ini
        $existingOffer = CommodityOffer::where('commodity_id', $commodity->id)
            ->where('trader_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved', 'accepted'])
            ->latest()
            ->first();

        return view('trader.commodities.show', compact('commodity', 'existingOffer'));
    }
}
