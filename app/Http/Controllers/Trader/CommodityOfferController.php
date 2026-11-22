<?php

namespace App\Http\Controllers\Trader;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommodityOfferRequest;
use App\Models\Commodity;
use App\Models\CommodityOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommodityOfferController extends Controller
{
    /**
     * Tampilkan daftar tawaran yang diajukan oleh pedagang yang sedang login.
     */
    public function index(Request $request): View
    {
        $offers = $request->user()
            ->offers()
            ->with(['commodity.farmer'])
            ->latest()
            ->paginate(10);

        return view('trader.offers.index', compact('offers'));
    }

    /**
     * Simpan pengajuan tawaran baru ke dalam database.
     */
    public function store(StoreCommodityOfferRequest $request, Commodity $commodity): RedirectResponse
    {
        $validated = $request->validated();

        CommodityOffer::create([
            'commodity_id' => $commodity->id,
            'trader_id' => $request->user()->id,
            'offered_daily_capacity' => $validated['offered_daily_capacity'],
            'offered_price' => $validated['offered_price'],
            'status' => 'pending',
        ]);

        return redirect()->route('trader.offers.index')
            ->with('success', "Tawaran kontrak untuk komoditas '{$commodity->name}' berhasil diajukan dan sedang menunggu persetujuan petani.");
    }

    /**
     * Batalkan tawaran selama statusnya masih 'pending'.
     */
    public function destroy(Request $request, CommodityOffer $offer): RedirectResponse
    {
        Gate::authorize('delete', $offer);

        if ($offer->status !== 'pending') {
            return back()->with('error', 'Hanya tawaran yang berstatus menunggu (pending) yang dapat dibatalkan.');
        }

        $offer->delete();

        return redirect()->route('trader.offers.index')
            ->with('success', 'Tawaran kontrak berhasil dibatalkan.');
    }
}
