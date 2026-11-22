<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommodityRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki izin untuk memperbarui komoditas ini.
     */
    public function authorize(): bool
    {
        $commodity = $this->route('commodity');

        return $commodity && $this->user()?->can('update', $commodity);
    }

    /**
     * Aturan validasi untuk pembaruan komoditas.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'initial_daily_capacity' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'estimated_harvest_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:open,closed'],
        ];
    }

    /**
     * Validasi lanjutan terhadap kapasitas yang sudah terkunci oleh kesepakatan aktif.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $commodity = $this->route('commodity');
            if ($commodity && $this->has('initial_daily_capacity')) {
                $newInitial = (float) $this->input('initial_daily_capacity');
                $oldInitial = (float) $commodity->initial_daily_capacity;
                $oldRemaining = (float) $commodity->remaining_daily_capacity;
                $locked = round($oldInitial - $oldRemaining, 2);

                if ($locked > 0 && $newInitial < $locked) {
                    $validator->errors()->add(
                        'initial_daily_capacity',
                        "Kapasitas awal tidak boleh lebih kecil dari kapasitas yang sedang terkunci oleh tawaran aktif ({$locked} {$commodity->unit})."
                    );
                }
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
            'name.required' => 'Nama komoditas wajib diisi.',
            'unit.required' => 'Satuan komoditas wajib diisi.',
            'initial_daily_capacity.required' => 'Estimasi kapasitas harian awal wajib diisi.',
            'initial_daily_capacity.numeric' => 'Estimasi kapasitas harian harus berupa angka desimal.',
            'initial_daily_capacity.gt' => 'Estimasi kapasitas harian harus lebih besar dari 0.',
            'estimated_harvest_date.required' => 'Estimasi tanggal masuk panen wajib diisi.',
            'estimated_harvest_date.date' => 'Format estimasi tanggal masuk panen tidak valid.',
        ];
    }
}
