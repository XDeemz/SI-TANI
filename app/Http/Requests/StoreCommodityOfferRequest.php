<?php

namespace App\Http\Requests;

use App\Models\CommodityOffer;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommodityOfferRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki izin untuk mengajukan tawaran.
     * Aturan Anti-Self-Offer & Status Terbuka.
     */
    public function authorize(): bool
    {
        $commodity = $this->route('commodity');

        if (! $commodity) {
            return false;
        }

        // Anti-Self-Offer: dilarang menawar komoditas milik sendiri
        if ((int) $commodity->farmer_id === (int) $this->user()?->id) {
            return false;
        }

        // Status komoditas wajib 'open'
        if ($commodity->status !== 'open') {
            return false;
        }

        return true;
    }

    /**
     * Aturan validasi input pengajuan tawaran.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'offered_daily_capacity' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'offered_price' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
        ];
    }

    /**
     * Validasi lanjutan terhadap kapasitas tersisa dan duplikasi tawaran aktif.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $commodity = $this->route('commodity');

            if (! $commodity) {
                return;
            }

            $offeredCapacity = (float) $this->input('offered_daily_capacity');
            $remainingCapacity = (float) $commodity->remaining_daily_capacity;

            // Kapasitas tidak boleh melebihi sisa kapasitas komoditas
            if ($remainingCapacity <= 0) {
                $validator->errors()->add(
                    'offered_daily_capacity',
                    'Sisa kapasitas harian komoditas ini sudah habis.'
                );
            } elseif ($offeredCapacity > $remainingCapacity) {
                $validator->errors()->add(
                    'offered_daily_capacity',
                    "Kapasitas yang ditawarkan ({$offeredCapacity} {$commodity->unit}) tidak boleh melebihi sisa kapasitas harian komoditas saat ini ({$remainingCapacity} {$commodity->unit}/hari)."
                );
            }

            // Cegah pedagang mengajukan lebih dari 1 tawaran aktif (pending atau approved/accepted)
            $hasActiveOffer = CommodityOffer::where('commodity_id', $commodity->id)
                ->where('trader_id', $this->user()?->id)
                ->whereIn('status', ['pending', 'approved', 'accepted'])
                ->exists();

            if ($hasActiveOffer) {
                $validator->errors()->add(
                    'offered_daily_capacity',
                    'Anda sudah memiliki tawaran aktif yang sedang menunggu atau disetujui pada komoditas ini.'
                );
            }
        });
    }

    /**
     * Pesan kesalahan khusus untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'offered_daily_capacity.required' => 'Estimasi kapasitas harian yang ditawarkan wajib diisi.',
            'offered_daily_capacity.numeric' => 'Estimasi kapasitas harian harus berupa angka desimal.',
            'offered_daily_capacity.gt' => 'Estimasi kapasitas harian harus lebih besar dari 0.',
            'offered_price.required' => 'Estimasi harga penawaran wajib diisi.',
            'offered_price.numeric' => 'Estimasi harga penawaran harus berupa angka desimal.',
            'offered_price.gt' => 'Estimasi harga penawaran harus lebih besar dari 0.',
        ];
    }
}
