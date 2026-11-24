<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\RecapPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommodityOfferController extends Controller
{
    /**
     * Tampilkan daftar tawaran yang masuk untuk seluruh komoditas milik petani.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $farmerCommodityIds = $user->commodities()->pluck('id');

        $query = CommodityOffer::with(['commodity', 'trader', 'recapPeriod'])
            ->whereIn('commodity_id', $farmerCommodityIds);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $offers = $query->latest()->paginate(10)->withQueryString();

        $counts = [
            'all' => CommodityOffer::whereIn('commodity_id', $farmerCommodityIds)->count(),
            'pending' => CommodityOffer::whereIn('commodity_id', $farmerCommodityIds)->where('status', 'pending')->count(),
            'approved' => CommodityOffer::whereIn('commodity_id', $farmerCommodityIds)->where('status', 'approved')->count(),
            'rejected' => CommodityOffer::whereIn('commodity_id', $farmerCommodityIds)->where('status', 'rejected')->count(),
        ];

        return view('farmer.offers.index', compact('offers', 'counts'));
    }

    /**
     * Setujui tawaran pedagang dengan transaksi database terkunci dan pembentukan periode rekap.
     */
    public function accept(Request $request, CommodityOffer $offer): RedirectResponse
    {
        Gate::authorize('accept', $offer);

        $validated = $request->validate([
            'farmer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($offer, $validated) {
            // Kunci baris komoditas dengan lockForUpdate untuk mencegah race condition / overbooking
            $commodity = Commodity::where('id', $offer->commodity_id)->lockForUpdate()->first();

            if (! $commodity) {
                return back()->with('error', 'Komoditas tidak ditemukan.');
            }

            if ($commodity->status !== 'open') {
                return back()->with('error', 'Komoditas sedang ditutup (closed), tawaran tidak dapat disetujui.');
            }

            $offeredCapacity = (float) $offer->offered_daily_capacity;
            $remainingCapacity = (float) $commodity->remaining_daily_capacity;

            // Validasi ketersediaan kapasitas harian
            if ($remainingCapacity < $offeredCapacity) {
                return back()->with(
                    'error',
                    "Sisa kapasitas harian komoditas tidak mencukupi untuk menyetujui tawaran ini (Sisa: {$remainingCapacity} {$commodity->unit}/hari, Dibutuhkan: {$offeredCapacity} {$commodity->unit}/hari)."
                );
            }

            // Hitung kapasitas yang dikunci dan kurangi sisa kapasitas komoditas
            $locked = min($remainingCapacity, $offeredCapacity);
            $commodity->remaining_daily_capacity = max(0, round($remainingCapacity - $offeredCapacity, 2));
            $commodity->save();

            // Ubah status tawaran menjadi approved
            $offer->update([
                'status' => 'approved',
                'farmer_notes' => $validated['farmer_notes'] ?? null,
                'responded_at' => now(),
            ]);

            // Buat record RecapPeriod baru
            RecapPeriod::create([
                'commodity_offer_id' => $offer->id,
                'commodity_id' => $commodity->id,
                'farmer_id' => $commodity->farmer_id,
                'trader_id' => $offer->trader_id,
                'locked_capacity' => $locked,
                'status' => 'ready',
                'total_amount' => 0.00,
            ]);

            return back()->with(
                'success',
                "Tawaran dari {$offer->trader->name} berhasil disetujui. Kuota kapasitas komoditas telah dialokasikan dan ruang rekapitulasi telah dibuat."
            );
        });
    }

    /**
     * Tolak tawaran pedagang tanpa mengurangi kapasitas komoditas.
     */
    public function reject(Request $request, CommodityOffer $offer): RedirectResponse
    {
        Gate::authorize('reject', $offer);

        $validated = $request->validate([
            'farmer_notes' => ['required', 'string', 'max:1000'],
        ]);

        $offer->update([
            'status' => 'rejected',
            'farmer_notes' => $validated['farmer_notes'],
            'responded_at' => now(),
        ]);

        return back()->with(
            'success',
            "Tawaran dari {$offer->trader->name} berhasil ditolak."
        );
    }
}
