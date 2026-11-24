<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('rekap.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1 transition">
                        &larr; Ruang Rekapitulasi
                    </a>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-xs text-gray-500">ID Kontrak #{{ $recapPeriod->id }}</span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="inline-block px-2 py-0.5 text-[11px] font-semibold {{ $isTrader ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }} rounded-full">
                        {{ $isTrader ? 'Akses Pedagang' : 'Mode Pantau Petani — Read-Only' }}
                    </span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>Lembar Rekap: {{ $recapPeriod->commodity->name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Ruang pencatatan setoran panen dan perhitungan tagihan harian terisolasi untuk pasangan 1 Petani – 1 Pedagang.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('rekap.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition shadow-sm">
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
                    <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Indikator Peran Kontekstual Saat Ini --}}
            @if ($isTrader)
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-center justify-between text-indigo-950">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 block">Indikator Akses: Pedagang</span>
                            <p class="text-xs text-indigo-900 mt-0.5">
                                Anda memiliki wewenang untuk mengontrol siklus periode (Mulai & Akhiri Rekap) serta melakukan pencatatan harian bertahap pada lembar kerja sheet.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-between text-emerald-950">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 block">Indikator Akses: Petani (Mode Pantau — Read-Only)</span>
                            <p class="text-xs text-emerald-900 mt-0.5">
                                Anda dapat memantau realisasi setoran harian yang dicatat oleh pedagang. Tombol pelunasan akan aktif setelah pedagang mengakhiri periode rekap.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Kartu Identitas Ruang Rekap Terisolasi (F-07) --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 overflow-hidden">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                Identitas Ruang Rekap Terisolasi
                            </span>
                            <span class="text-gray-300">&bull;</span>
                            {{-- Badge Status Periode / Pembayaran --}}
                            @if ($recapPeriod->status === 'ready')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                    Siap Dimulai
                                </span>
                            @elseif ($recapPeriod->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    Aktif
                                </span>
                            @elseif ($recapPeriod->status === 'closed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Menunggu Pembayaran
                                </span>
                            @elseif ($recapPeriod->status === 'paid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                    <svg class="w-3.5 h-3.5 mr-1 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Lunas / Selesai
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                            {{ $recapPeriod->commodity->name }}
                        </h1>
                        <p class="text-xs text-gray-500 mt-1">
                            Satuan Hasil Panen: <span class="font-semibold text-gray-700">{{ $recapPeriod->commodity->unit }}</span>
                            @if ($recapPeriod->started_at)
                                &bull; Dimulai: <span class="font-medium text-gray-700">{{ $recapPeriod->started_at->translatedFormat('d M Y, H:i') }}</span>
                            @endif
                            @if ($recapPeriod->ended_at)
                                &bull; Berakhir: <span class="font-medium text-gray-700">{{ $recapPeriod->ended_at->translatedFormat('d M Y, H:i') }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Dua Kolom Metrik Ringkasan --}}
                    <div class="grid grid-cols-2 gap-4 shrink-0">
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 text-center min-w-[140px]">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Kapasitas Terkunci</span>
                            <span class="text-base font-bold text-gray-900 mt-1 block">
                                {{ number_format($recapPeriod->locked_capacity, 2, ',', '.') }} {{ $recapPeriod->commodity->unit }}/hari
                            </span>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 text-center min-w-[160px]">
                            <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">Total Akumulasi</span>
                            <span class="text-base font-extrabold text-emerald-800 mt-1 block">
                                Rp {{ number_format($recapPeriod->total_amount, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pasangan Kontrak Terkait --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 text-xs">
                    {{-- Petani --}}
                    <div class="p-4 rounded-xl border {{ $isFarmer ? 'border-emerald-300 bg-emerald-50/40' : 'border-gray-200 bg-gray-50/60' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-gray-700 flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
                                <span>👨‍🌾</span>
                                <span>Petani (Pemilik Komoditas)</span>
                            </span>
                            @if ($isFarmer)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 font-bold text-[10px]">Akun Anda</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <div class="text-sm font-bold text-gray-900">{{ $recapPeriod->farmer->name }}</div>
                            <div class="text-gray-500">
                                Kontak: <span class="font-medium text-gray-700">{{ $recapPeriod->farmer->phone ?? $recapPeriod->farmer->email }}</span>
                            </div>
                            @if ($recapPeriod->farmer->payment_info)
                                <div class="text-gray-500">
                                    Info Bayar: <span class="font-medium text-gray-700">{{ $recapPeriod->farmer->payment_info }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pedagang --}}
                    <div class="p-4 rounded-xl border {{ $isTrader ? 'border-indigo-300 bg-indigo-50/40' : 'border-gray-200 bg-gray-50/60' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-gray-700 flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
                                <span>🏪</span>
                                <span>Pedagang (Mitra Pembeli)</span>
                            </span>
                            @if ($isTrader)
                                <span class="px-2 py-0.5 rounded-full bg-indigo-200 text-indigo-900 font-bold text-[10px]">Akun Anda</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <div class="text-sm font-bold text-gray-900">{{ $recapPeriod->trader->name }}</div>
                            <div class="text-gray-500">
                                Kontak: <span class="font-medium text-gray-700">{{ $recapPeriod->trader->phone ?? $recapPeriod->trader->email }}</span>
                            </div>
                            @if ($recapPeriod->offer)
                                <div class="text-gray-500">
                                    Harga Penawaran Acuan: <span class="font-medium text-emerald-700">Rp {{ number_format($recapPeriod->offer->offered_price, 2, ',', '.') }}/{{ $recapPeriod->commodity->unit }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Area Kontrol Siklus Periode (F-08 & F-11) --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-base text-gray-900 flex items-center gap-2">
                            <span>⏱️ Kontrol Siklus Periode Rekap</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Alur status transaksi: <strong class="text-gray-700">Siap Dimulai</strong> &rarr; <strong class="text-gray-700">Aktif</strong> &rarr; <strong class="text-gray-700">Menunggu Pembayaran</strong> &rarr; <strong class="text-gray-700">Lunas / Selesai</strong>.
                        </p>
                        @if ($recapPeriod->started_at || $recapPeriod->ended_at)
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-600">
                                @if ($recapPeriod->started_at)
                                    <span class="inline-flex items-center gap-1 bg-gray-100 px-2.5 py-1 rounded-md">
                                        <span class="text-gray-500">▶️ Waktu Mulai:</span>
                                        <span class="font-semibold text-gray-800">{{ $recapPeriod->started_at->translatedFormat('d M Y, H:i') }}</span>
                                    </span>
                                @endif
                                @if ($recapPeriod->ended_at)
                                    <span class="inline-flex items-center gap-1 bg-gray-100 px-2.5 py-1 rounded-md">
                                        <span class="text-gray-500">⏹️ Waktu Berakhir:</span>
                                        <span class="font-semibold text-gray-800">{{ $recapPeriod->ended_at->translatedFormat('d M Y, H:i') }}</span>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Kontrol Aksi Transisi Status --}}
                    <div>
                        @if ($isTrader)
                            {{-- KONTROL KHUSUS PEDAGANG (TEMA INDIGO) --}}
                            @if ($recapPeriod->status === 'ready')
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <div class="text-xs text-gray-500 max-w-xs text-left sm:text-right">
                                        Mulai periode saat setoran pertama masuk, atau mulai lalu langsung akhiri jika ingin membatalkan kesepakatan.
                                    </div>
                                    <form action="{{ route('rekap.start', $recapPeriod) }}" method="POST" onsubmit="return confirm('Mulai periode rekapitulasi sekarang? Anda akan dapat mencatat setoran harian pada lembar kerja sheet.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition shadow-sm gap-2 whitespace-nowrap">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Mulai Periode
                                        </button>
                                    </form>
                                </div>
                            @elseif ($recapPeriod->status === 'active')
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <div class="text-xs text-gray-500 max-w-xs text-left sm:text-right">
                                        Akhiri periode ketika seluruh rangkaian setoran panen selesai. Tindakan ini memfinalisasi total tagihan dan mengunci tabel rekap secara permanen.
                                    </div>
                                    <form action="{{ route('rekap.close', $recapPeriod) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri periode rekapitulasi? Seluruh baris rekap harian akan dikunci permanen dan total tagihan akan difinalisasi.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-amber-600 text-white rounded-lg text-xs font-semibold hover:bg-amber-700 transition shadow-sm gap-2 whitespace-nowrap">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                            </svg>
                                            Akhiri Periode
                                        </button>
                                    </form>
                                </div>
                            @elseif ($recapPeriod->status === 'closed')
                                <div class="px-4 py-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                    <div class="font-bold flex items-center gap-1.5 text-amber-800">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        Periode Telah Ditutup & Tabel Rekap Terkunci (Read-Only)
                                    </div>
                                    <p class="text-amber-700 mt-0.5">
                                        Total tagihan telah difinalisasi. Menunggu konfirmasi pelunasan oleh petani mitra.
                                    </p>
                                </div>
                            @elseif ($recapPeriod->status === 'paid')
                                <div class="px-4 py-2.5 rounded-lg bg-purple-50 border border-purple-200 text-purple-900 text-xs">
                                    <div class="font-bold flex items-center gap-1.5 text-purple-800">
                                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Transaksi Selesai & Lunas
                                    </div>
                                    <p class="text-purple-700 mt-0.5">
                                        Kapasitas komoditas yang terkunci telah dikembalikan secara otomatis.
                                    </p>
                                </div>
                            @endif
                        @else
                            {{-- TAMPILAN UNTUK PETANI (TEMA EMERALD - READ-ONLY) --}}
                            @if ($recapPeriod->status === 'ready')
                                <div class="px-4 py-2.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-900 text-xs">
                                    <div class="font-semibold flex items-center gap-1.5 text-blue-800">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        Status: Siap Dimulai
                                    </div>
                                    <p class="text-blue-700 mt-0.5">
                                        Menunggu pedagang mitra memulai periode rekap saat pasokan hasil panen pertama siap dikirim.
                                    </p>
                                </div>
                            @elseif ($recapPeriod->status === 'active')
                                <div class="px-4 py-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs">
                                    <div class="font-semibold flex items-center gap-1.5 text-emerald-800">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Status: Periode Aktif Berjalan
                                    </div>
                                    <p class="text-emerald-700 mt-0.5">
                                        Pedagang sedang aktif mencatat setoran harian. Anda dapat memantau realisasi bobot dan subtotal secara transparan.
                                    </p>
                                </div>
                            @elseif ($recapPeriod->status === 'closed')
                                <div class="px-4 py-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                    <div class="font-semibold flex items-center gap-1.5 text-amber-800">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        Status: Menunggu Pembayaran
                                    </div>
                                    <p class="text-amber-700 mt-0.5">
                                        Pedagang telah mengakhiri periode dan tabel rekap terkunci. Anda dapat mengonfirmasi pelunasan setelah dana diterima.
                                    </p>
                                </div>
                            @elseif ($recapPeriod->status === 'paid')
                                <div class="px-4 py-2.5 rounded-lg bg-purple-50 border border-purple-200 text-purple-900 text-xs">
                                    <div class="font-semibold flex items-center gap-1.5 text-purple-800">
                                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Status: Transaksi Lunas / Selesai
                                    </div>
                                    <p class="text-purple-700 mt-0.5">
                                        Pembayaran telah lunas dan kuota kapasitas komoditas telah dikembalikan.
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tabel Riwayat Baris Rekap Harian (F-09 - Sheet View) --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                            <span>📋 Catatan Realisasi Harian (Tampilan Sheet)</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Daftar kronologis setoran panen dan perhitungan subtotal aktual harian per tanggal transaksi.
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                            {{ $recapPeriod->recapItems->count() }} Baris Catatan
                        </span>
                    </div>
                </div>

                {{-- Tabel Baris Sheet --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Tanggal Transaksi
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Bobot Bersih ({{ $recapPeriod->commodity->unit }})
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Harga Aktual / {{ $recapPeriod->commodity->unit }}
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Subtotal
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Catatan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($recapPeriod->recapItems as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($item->transaction_date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900">
                                        {{ number_format($item->net_weight, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700">
                                        Rp {{ number_format($item->actual_price, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-emerald-700">
                                        Rp {{ number_format($item->subtotal, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 whitespace-normal">
                                        {{ $item->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-gray-700 text-sm">Belum ada baris setoran panen tercatat</p>
                                        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                                            Pencatatan realisasi bobot harian dan formulir berbasis sheet akan tersedia secara aktif pada Sesi 3.3.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($recapPeriod->recapItems->isNotEmpty())
                            <tfoot class="bg-gray-50 font-bold text-gray-900 border-t-2 border-gray-200">
                                <tr>
                                    <td class="px-6 py-4">Total</td>
                                    <td class="px-6 py-4 text-right">
                                        {{ number_format($recapPeriod->recapItems->sum('net_weight'), 2, ',', '.') }} {{ $recapPeriod->commodity->unit }}
                                    </td>
                                    <td class="px-6 py-4 text-right">-</td>
                                    <td class="px-6 py-4 text-right text-emerald-800 text-base">
                                        Rp {{ number_format($recapPeriod->total_amount, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
