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
                    {{ __('Tambah Komoditas Baru') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Daftarkan komoditas agrikultur hasil panen Anda untuk ditawarkan kepada pedagang mitra.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6 sm:p-8">

                <form method="POST" action="{{ route('farmer.commodities.store') }}" class="space-y-6">
                    @csrf

                    {{-- Nama Komoditas --}}
                    <div>
                        <x-input-label for="name" :value="__('Nama Komoditas')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="Contoh: Beras Pandan Wangi, Bawang Merah Super, dsb." required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    {{-- Satuan & Estimasi Kapasitas Harian Awal (Grid 2 kolom) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="unit" :value="__('Satuan Ukuran')" />
                            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', 'kg')" placeholder="kg, ton, ikat, kuintal" required />
                            <p class="mt-1 text-xs text-gray-500">Satuan bobot yang digunakan saat setoran harian (default: kg).</p>
                            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                        </div>

                        <div>
                            <x-input-label for="initial_daily_capacity" :value="__('Estimasi Kapasitas Harian Awal')" />
                            <x-text-input id="initial_daily_capacity" name="initial_daily_capacity" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('initial_daily_capacity')" placeholder="Contoh: 150.00" required />
                            <p class="mt-1 text-xs text-gray-500">Kapasitas harian acuan awal. Sisa kapasitas harian akan otomatis diisi sama.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('initial_daily_capacity')" />
                        </div>
                    </div>

                    {{-- Estimasi Tanggal Masuk Masa Panen & Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="estimated_harvest_date" :value="__('Estimasi Tanggal Masuk Panen')" />
                            <x-text-input id="estimated_harvest_date" name="estimated_harvest_date" type="date" class="mt-1 block w-full" :value="old('estimated_harvest_date')" required />
                            <p class="mt-1 text-xs text-gray-500">Perkiraan tanggal dimulainya setoran hasil panen.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('estimated_harvest_date')" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status Ketersediaan Awal')" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm">
                                <option value="open" {{ old('status', 'open') === 'open' ? 'selected' : '' }}>Terbuka (Menerima tawaran pedagang)</option>
                                <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Ditutup (Belum menerima tawaran)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Dapat diubah secara manual kapan saja oleh Anda.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div>
                        <x-input-label for="notes" :value="__('Catatan Tambahan (Opsional)')" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm" placeholder="Contoh: Lokasi panen di Desa Subur, pengambilan pagi hari pukul 06.00 WIB, kualitas sortiran grade A.">{{ old('notes') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Berikan detail kualitas atau instruksi khusus bagi pedagang.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('farmer.commodities.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition shadow-sm">
                            Simpan Komoditas
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
