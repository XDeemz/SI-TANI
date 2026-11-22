<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('trader.commodities.index') }}" class="text-xs text-gray-500 hover:text-indigo-700 transition">
                        &larr; Kembali ke Katalog Komoditas
                    </a>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Detail Komoditas & Ajukan Tawaran') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Tinjau informasi hasil panen dan ajukan estimasi kapasitas harian serta harga acuan kontrak.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kolom Kiri: Informasi Komoditas & Petani --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <div class="border-b border-gray-100 pb-4">
                            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full mb-2">
                                Komoditas Terbuka
                            </span>
                            <h3 class="text-xl font-bold text-gray-900">{{ $commodity->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Satuan: <span class="font-semibold text-gray-700">{{ $commodity->unit }}</span></p>
                        </div>

                        {{-- Ringkasan Kapasitas --}}
                        <div class="space-y-3 text-sm">
                            <div class="p-3 bg-indigo-50/60 rounded-lg border border-indigo-100">
                                <span class="text-xs text-indigo-700 font-medium block">Sisa Kapasitas Harian Acuan:</span>
                                <span class="text-lg font-bold text-indigo-900">
                                    {{ number_format($commodity->remaining_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari
                                </span>
                                <span class="text-xs text-gray-500 block mt-0.5">
                                    dari total {{ number_format($commodity->initial_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari
                                </span>
                            </div>

                            <div class="flex justify-between text-xs py-1">
                                <span class="text-gray-500">Estimasi Masuk Panen:</span>
                                <span class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::parse($commodity->estimated_harvest_date)->translatedFormat('d F Y') }}
                                </span>
                            </div>
                        </div>

                        {{-- Profil Petani Pemilik --}}
                        <div class="pt-4 border-t border-gray-100 text-xs space-y-2">
                            <span class="font-bold text-gray-700 uppercase tracking-wider block">Petani Pemilik</span>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                    {{ substr($commodity->farmer->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $commodity->farmer->name }}</p>
                                    @if ($commodity->farmer->phone)
                                        <p class="text-gray-500">📞 {{ $commodity->farmer->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Catatan Petani --}}
                        @if ($commodity->notes)
                            <div class="pt-4 border-t border-gray-100 text-xs">
                                <span class="font-bold text-gray-700 uppercase tracking-wider block mb-1">Catatan Tambahan Petani</span>
                                <p class="text-gray-600 bg-gray-50 p-3 rounded-lg leading-relaxed">
                                    {{ $commodity->notes }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan: Form Pengajuan Tawaran atau Status Tawaran Aktif --}}
                <div class="lg:col-span-2">
                    @if ($existingOffer)
                        {{-- Tampilan jika Pedagang Sudah Memiliki Tawaran Aktif --}}
                        <div class="bg-white rounded-xl shadow-sm border border-indigo-200 p-6 sm:p-8 space-y-6">
                            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                                <div class="p-3 bg-indigo-100 text-indigo-700 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Anda Memiliki Tawaran Aktif</h3>
                                    <p class="text-xs text-gray-500">Tawaran untuk komoditas ini telah dikirimkan sebelumnya.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <span class="text-xs text-gray-500 block">Kapasitas Harian Ditawarkan</span>
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ number_format($existingOffer->offered_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari
                                    </span>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <span class="text-xs text-gray-500 block">Estimasi Harga Ditawarkan</span>
                                    <span class="text-lg font-bold text-emerald-700">
                                        Rp {{ number_format($existingOffer->offered_price, 2, ',', '.') }}/{{ $commodity->unit }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-indigo-50/50 rounded-lg text-xs">
                                <div>
                                    <span class="text-gray-500 block">Status Tawaran Saat Ini:</span>
                                    <span class="font-bold text-sm {{ $existingOffer->status === 'approved' ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $existingOffer->status === 'approved' ? 'Disetujui Petani' : 'Menunggu Persetujuan' }}
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('trader.offers.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs hover:bg-indigo-700 transition">
                                        Kelola di Halaman Tawaran Saya &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Formulir Pengajuan Tawaran Baru --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Form Pengajuan Penawaran Kontrak</h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    Tentukan estimasi alokasi kapasitas panen harian yang Anda butuhkan dan harga acuan yang Anda ajukan kepada petani.
                                </p>
                            </div>

                            {{-- Domain Notice Banner --}}
                            <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-900 space-y-1">
                                <span class="font-bold block">💡 Prinsip Penawaran Pra-Panen SI-TANI:</span>
                                <p class="leading-relaxed text-amber-800">
                                    Kapasitas harian dan harga yang diajukan hanyalah <strong>bahan pertimbangan awal</strong> bagi petani. Sesuai kesepakatan fleksibel agrikultur, tagihan akhir dihitung berdasarkan akumulasi pencatatan harian aktual saat panen berlangsung.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('trader.offers.store', $commodity) }}" class="space-y-6">
                                @csrf

                                {{-- Input Kapasitas Harian yang Ditawarkan --}}
                                <div>
                                    <x-input-label for="offered_daily_capacity" :value="__('Estimasi Kapasitas Harian yang Dibutuhkan (' . $commodity->unit . '/hari)')" />
                                    <x-text-input id="offered_daily_capacity" name="offered_daily_capacity" type="number" step="0.01" min="0.01" max="{{ $commodity->remaining_daily_capacity }}" class="mt-1 block w-full" :value="old('offered_daily_capacity')" placeholder="Contoh: 50.00" required autofocus />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Maksimal <span class="font-semibold text-indigo-700">{{ number_format($commodity->remaining_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari</span> sesuai sisa kapasitas saat ini.
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('offered_daily_capacity')" />
                                </div>

                                {{-- Input Harga Penawaran --}}
                                <div>
                                    <x-input-label for="offered_price" :value="__('Estimasi Harga Penawaran Acuan (Rp per ' . $commodity->unit . ')') " />
                                    <x-text-input id="offered_price" name="offered_price" type="number" step="0.01" min="1" class="mt-1 block w-full" :value="old('offered_price')" placeholder="Contoh: 15000.00" required />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Harga acuan awal kesepakatan (format nominal Rupiah).
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('offered_price')" />
                                </div>

                                {{-- Tombol Submit --}}
                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                    <a href="{{ route('trader.commodities.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                                        Batal
                                    </a>
                                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-sm">
                                        Kirim Penawaran Kontrak
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
