<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommodityRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki izin untuk membuat komoditas.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi untuk penyimpanan komoditas baru.
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
