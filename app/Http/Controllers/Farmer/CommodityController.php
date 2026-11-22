<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommodityRequest;
use App\Http\Requests\UpdateCommodityRequest;
use App\Models\Commodity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommodityController extends Controller
{
    /**
     * Tampilkan daftar komoditas yang dimiliki oleh petani yang sedang login.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Commodity::class);

        $commodities = $request->user()
            ->commodities()
            ->withCount(['offers', 'recapPeriods'])
            ->latest()
            ->paginate(10);

        return view('farmer.commodities.index', compact('commodities'));
    }

    /**
     * Tampilkan formulir untuk menambahkan komoditas baru.
     */
    public function create(): View
    {
        Gate::authorize('create', Commodity::class);

        return view('farmer.commodities.create');
    }

    /**
     * Simpan komoditas baru ke dalam database.
     */
    public function store(StoreCommodityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['farmer_id'] = $request->user()->id;
        $validated['remaining_daily_capacity'] = $validated['initial_daily_capacity'];
        $validated['status'] = $validated['status'] ?? 'open';

        Commodity::create($validated);

        return redirect()->route('farmer.commodities.index')
            ->with('success', 'Komoditas berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail atau arahkan ke halaman edit komoditas.
     */
    public function show(Commodity $commodity): RedirectResponse
    {
        Gate::authorize('view', $commodity);

        return redirect()->route('farmer.commodities.edit', $commodity);
    }

    /**
     * Tampilkan formulir untuk mengubah data komoditas.
     */
    public function edit(Commodity $commodity): View
    {
        Gate::authorize('update', $commodity);

        $lockedCapacity = max(0, round((float) $commodity->initial_daily_capacity - (float) $commodity->remaining_daily_capacity, 2));

        return view('farmer.commodities.edit', compact('commodity', 'lockedCapacity'));
    }

    /**
     * Perbarui data komoditas di dalam database.
     */
    public function update(UpdateCommodityRequest $request, Commodity $commodity): RedirectResponse
    {
        Gate::authorize('update', $commodity);

        $validated = $request->validated();

        $newInitial = (float) $validated['initial_daily_capacity'];
        $oldInitial = (float) $commodity->initial_daily_capacity;
        $locked = max(0, round($oldInitial - (float) $commodity->remaining_daily_capacity, 2));

        if ($locked > 0 && $newInitial < $locked) {
            return back()->withInput()->withErrors([
                'initial_daily_capacity' => "Kapasitas awal tidak boleh lebih kecil dari kapasitas yang sedang terkunci oleh tawaran aktif ({$locked} {$commodity->unit}).",
            ]);
        }

        $diff = $newInitial - $oldInitial;
        $newRemaining = max(0, round((float) $commodity->remaining_daily_capacity + $diff, 2));

        $commodity->update([
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'initial_daily_capacity' => $newInitial,
            'remaining_daily_capacity' => $newRemaining,
            'estimated_harvest_date' => $validated['estimated_harvest_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'] ?? $commodity->status,
        ]);

        return redirect()->route('farmer.commodities.index')
            ->with('success', 'Komoditas berhasil diperbarui.');
    }

    /**
     * Ubah status komoditas antara open dan closed secara instan.
     */
    public function toggleStatus(Commodity $commodity): RedirectResponse
    {
        Gate::authorize('toggleStatus', $commodity);

        $commodity->status = $commodity->status === 'open' ? 'closed' : 'open';
        $commodity->save();

        $statusLabel = $commodity->status === 'open' ? 'Terbuka (Open)' : 'Ditutup (Closed)';

        return back()->with('success', "Status komoditas '{$commodity->name}' berhasil diubah menjadi {$statusLabel}.");
    }

    /**
     * Hapus komoditas jika belum ada tawaran yang disetujui atau periode rekap aktif.
     */
    public function destroy(Commodity $commodity): RedirectResponse
    {
        Gate::authorize('delete', $commodity);

        $hasApprovedOffers = $commodity->offers()->whereIn('status', ['approved', 'accepted'])->exists();
        $hasActiveRecaps = $commodity->recapPeriods()->whereIn('status', ['ready', 'active', 'closed'])->exists();

        if ($hasApprovedOffers || $hasActiveRecaps) {
            return back()->with('error', 'Komoditas tidak dapat dihapus karena sudah memiliki kesepakatan tawaran yang disetujui atau periode rekapitulasi yang sedang berjalan.');
        }

        $commodity->delete();

        return redirect()->route('farmer.commodities.index')
            ->with('success', 'Komoditas berhasil dihapus.');
    }
}
