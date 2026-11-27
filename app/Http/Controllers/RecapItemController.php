<?php

namespace App\Http\Controllers;

use App\Models\RecapItem;
use App\Models\RecapPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecapItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Tambah baris rekap harian baru pada periode aktif oleh pedagang.
     */
    public function store(Request $request, RecapPeriod $recapPeriod): RedirectResponse
    {
        $this->authorize('store', [RecapItem::class, $recapPeriod]);

        $validated = $this->validateItem($request, $recapPeriod);

        DB::transaction(function () use ($recapPeriod, $validated): void {
            $subtotal = round((float) $validated['net_weight'] * (float) $validated['actual_price'], 2);

            $recapPeriod->recapItems()->create([
                'transaction_date' => $validated['transaction_date'],
                'net_weight' => $validated['net_weight'],
                'actual_price' => $validated['actual_price'],
                'subtotal' => $subtotal,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncTotalAmount($recapPeriod);
        });

        return back()->with('success', 'Baris rekap harian berhasil ditambahkan.');
    }

    /**
     * Perbarui baris rekap harian pada periode aktif oleh pedagang.
     */
    public function update(Request $request, RecapPeriod $recapPeriod, RecapItem $recapItem): RedirectResponse
    {
        $this->authorize('update', [$recapItem, $recapPeriod]);

        $validated = $this->validateItem($request, $recapPeriod, $recapItem);

        DB::transaction(function () use ($recapPeriod, $recapItem, $validated): void {
            $subtotal = round((float) $validated['net_weight'] * (float) $validated['actual_price'], 2);

            $recapItem->update([
                'transaction_date' => $validated['transaction_date'],
                'net_weight' => $validated['net_weight'],
                'actual_price' => $validated['actual_price'],
                'subtotal' => $subtotal,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncTotalAmount($recapPeriod);
        });

        return back()->with('success', 'Baris rekap harian berhasil diperbarui.');
    }

    /**
     * Hapus baris rekap harian pada periode aktif oleh pedagang.
     */
    public function destroy(RecapPeriod $recapPeriod, RecapItem $recapItem): RedirectResponse
    {
        $this->authorize('destroy', [$recapItem, $recapPeriod]);

        DB::transaction(function () use ($recapPeriod, $recapItem): void {
            $recapItem->delete();

            $this->syncTotalAmount($recapPeriod);
        });

        return back()->with('success', 'Baris rekap harian berhasil dihapus.');
    }

    /**
     * Validasi input baris rekap dengan keunikan tanggal per periode rekap.
     *
     * @return array<string, mixed>
     */
    private function validateItem(Request $request, RecapPeriod $recapPeriod, ?RecapItem $recapItem = null): array
    {
        $duplicateMessage = 'Tanggal transaksi tersebut sudah tercatat pada periode rekap ini. Pilih tanggal lain atau ubah baris yang sudah ada.';

        $uniqueDateRule = Rule::unique('recap_items', 'transaction_date')
            ->where('recap_period_id', $recapPeriod->id);

        if ($recapItem !== null) {
            $uniqueDateRule->ignore($recapItem->id);
        }

        // Handle SQLite date storage ('Y-m-d H:i:s' from Eloquent date cast) alongside Rule::unique.
        $uniqueDateCallback = function (string $attribute, mixed $value, \Closure $fail) use ($recapPeriod, $recapItem, $duplicateMessage): void {
            if (! is_string($value) || strtotime($value) === false) {
                return;
            }

            $exists = RecapItem::where('recap_period_id', $recapPeriod->id)
                ->whereDate('transaction_date', Carbon::parse($value)->toDateString())
                ->when($recapItem !== null, fn ($query) => $query->where('id', '!=', $recapItem->id))
                ->exists();

            if ($exists) {
                $fail($duplicateMessage);
            }
        };

        return $request->validate([
            'transaction_date' => ['required', 'date', $uniqueDateRule, $uniqueDateCallback],
            'net_weight' => ['required', 'numeric', 'min:0'],
            'actual_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal transaksi tidak valid.',
            'transaction_date.unique' => $duplicateMessage,
            'net_weight.required' => 'Bobot bersih wajib diisi.',
            'net_weight.numeric' => 'Bobot bersih harus berupa angka.',
            'net_weight.min' => 'Bobot bersih tidak boleh kurang dari 0.',
            'actual_price.required' => 'Harga aktual wajib diisi.',
            'actual_price.numeric' => 'Harga aktual harus berupa angka.',
            'actual_price.min' => 'Harga aktual tidak boleh kurang dari 0.',
            'notes.max' => 'Catatan maksimal 255 karakter.',
        ]);
    }

    /**
     * Sinkronkan kolom total_amount pada periode rekap dengan akumulasi subtotal item.
     */
    private function syncTotalAmount(RecapPeriod $recapPeriod): void
    {
        $recapPeriod->update([
            'total_amount' => round((float) $recapPeriod->recapItems()->sum('subtotal'), 2),
        ]);
    }
}
