<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('farmer.commodities.index') }}" class="text-xs text-gray-500 hover:text-emerald-700 transition">
                        &larr; Kembali ke Komoditas Saya
                    </a>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Ubah Komoditas: ') }} {{ $commodity->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Perbarui informasi kapasitas, tanggal panen, atau catatan komoditas ini.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Info Kapasitas Terkunci jika ada --}}
            @if ($lockedCapacity > 0)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="shrink-0 text-amber-500 mt-0.5">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ms-3">
                            <h3 class="text-sm font-semibold text-amber-800">
                                Kapasitas Sedang Terkunci oleh Kontrak Aktif
                            </h3>
                            <p class="text-xs text-amber-700 mt-1">
                                Sebesar <span class="font-bold">{{ number_format($lockedCapacity, 2, ',', '.') }} {{ $commodity->unit }}/hari</span> sedang dialokasikan untuk tawaran yang telah disetujui. Oleh karena itu, estimasi kapasitas harian awal yang baru tidak boleh kurang dari nilai tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6 sm:p-8">

                <form method="POST" action="{{ route('farmer.commodities.update', $commodity) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Nama Komoditas --}}
                    <div>
                        <x-input-label for="name" :value="__('Nama Komoditas')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $commodity->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    {{-- Satuan & Estimasi Kapasitas Harian (Grid 2 kolom) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="unit" :value="__('Satuan Ukuran')" />
                            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $commodity->unit)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                        </div>

                        <div>
                            <x-input-label for="initial_daily_capacity" :value="__('Estimasi Kapasitas Harian Awal')" />
                            <x-text-input id="initial_daily_capacity" name="initial_daily_capacity" type="number" step="0.01" min="{{ $lockedCapacity > 0 ? $lockedCapacity : '0.01' }}" class="mt-1 block w-full" :value="old('initial_daily_capacity', $commodity->initial_daily_capacity)" required />
                            <p class="mt-1 text-xs text-gray-500">
                                Sisa kapasitas saat ini: <span class="font-bold text-emerald-700">{{ number_format($commodity->remaining_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari</span>.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('initial_daily_capacity')" />
                        </div>
                    </div>

                    {{-- Estimasi Tanggal Masuk Panen & Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="estimated_harvest_date" :value="__('Estimasi Tanggal Masuk Panen')" />
                            <x-text-input id="estimated_harvest_date" name="estimated_harvest_date" type="date" class="mt-1 block w-full" :value="old('estimated_harvest_date', is_string($commodity->estimated_harvest_date) ? $commodity->estimated_harvest_date : $commodity->estimated_harvest_date?->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('estimated_harvest_date')" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status Ketersediaan')" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm">
                                <option value="open" {{ old('status', $commodity->status) === 'open' ? 'selected' : '' }}>Terbuka (Menerima tawaran pedagang)</option>
                                <option value="closed" {{ old('status', $commodity->status) === 'closed' ? 'selected' : '' }}>Ditutup (Belum menerima tawaran)</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div>
                        <x-input-label for="notes" :value="__('Catatan Tambahan (Opsional)')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm">{{ old('notes', $commodity->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('farmer.commodities.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
