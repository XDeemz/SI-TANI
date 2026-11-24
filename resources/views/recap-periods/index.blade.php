<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-teal-100 text-teal-800 rounded-full">
                        Modul Rekapitulasi (F-07)
                    </span>
                    <span class="text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500">Tampilan Sheet Terpisah per Pasangan Kontrak</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Ruang Rekapitulasi Kontrak') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Kelola dan pantau lembar pencatatan setoran panen harian yang terisolasi untuk setiap pasangan (1 Petani – 1 Pedagang).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition shadow-sm">
                    &larr; Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ activeTab: '{{ $traderRecaps->isNotEmpty() ? 'trader' : ($farmerRecaps->isNotEmpty() ? 'farmer' : 'trader') }}' }">
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

            {{-- Tab Switcher Kontekstual --}}
            <div class="bg-white p-2 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row gap-2">
                {{-- Tab Sebagai Pedagang --}}
                <button type="button"
                        @click="activeTab = 'trader'"
                        :class="activeTab === 'trader' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                        class="flex-1 py-3 px-4 rounded-lg font-semibold text-sm transition flex items-center justify-center gap-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Sebagai Pedagang</span>
                    <span :class="activeTab === 'trader' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700'"
                          class="text-xs px-2.5 py-0.5 rounded-full font-bold">
                        {{ $traderRecaps->count() }} Ruang
                    </span>
                </button>

                {{-- Tab Sebagai Petani --}}
                <button type="button"
                        @click="activeTab = 'farmer'"
                        :class="activeTab === 'farmer' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                        class="flex-1 py-3 px-4 rounded-lg font-semibold text-sm transition flex items-center justify-center gap-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>Sebagai Petani</span>
                    <span :class="activeTab === 'farmer' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'"
                          class="text-xs px-2.5 py-0.5 rounded-full font-bold">
                        {{ $farmerRecaps->count() }} Ruang
                    </span>
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- KONTEN TAB: SEBAGAI PEDAGANG (TEMA INDIGO) --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'trader'" class="space-y-6">
                {{-- Banner Kontekstual Pedagang --}}
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start gap-3 text-indigo-950">
                    <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-xs leading-relaxed">
                        <span class="font-bold text-sm block mb-0.5 text-indigo-900">Peran Anda: Pedagang</span>
                        Pada ruang rekap berikut, Anda bertindak sebagai pembeli. Anda memiliki wewenang untuk <strong>memulai periode</strong>, <strong>mengisi baris setoran panen harian (sheet)</strong>, dan <strong>mengakhiri periode</strong> untuk mengunci tagihan akhir.
                    </div>
                </div>

                @if ($traderRecaps->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Ruang Rekap Sebagai Pedagang</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Ruang rekapitulasi akan otomatis dibuat begitu penawaran kontrak yang Anda ajukan disetujui oleh petani pemilik komoditas.
                        </p>
                        <div class="mt-6 flex justify-center gap-3">
                            <a href="{{ route('trader.commodities.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-indigo-700 shadow-sm transition">
                                Eksplorasi Komoditas &rarr;
                            </a>
                            <a href="{{ route('trader.offers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-gray-50 transition shadow-sm">
                                Cek Tawaran Saya
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($traderRecaps as $period)
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between border-t-4 border-t-indigo-600">
                                <div class="p-5">
                                    {{-- Header Kartu: Komoditas & Badge Status --}}
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-base leading-tight">
                                                {{ $period->commodity->name }}
                                            </h3>
                                            <span class="text-xs text-gray-500 font-medium">
                                                Satuan: {{ $period->commodity->unit }}
                                            </span>
                                        </div>

                                        {{-- Badge Status Periode & Pembayaran --}}
                                        <div>
                                            @if ($period->status === 'ready')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800" title="Siap Dimulai">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                                    Siap Dimulai
                                                </span>
                                            @elseif ($period->status === 'active')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800" title="Periode Aktif">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                    Aktif
                                                </span>
                                            @elseif ($period->status === 'closed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800" title="Menunggu Pembayaran">
                                                    Menunggu Pembayaran
                                                </span>
                                            @elseif ($period->status === 'paid')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800" title="Lunas / Selesai">
                                                    <svg class="w-3 h-3 mr-1 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Lunas / Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Identitas Lengkap Pasangan Kontrak --}}
                                    <div class="bg-gray-50 rounded-lg p-3 space-y-1.5 text-xs border border-gray-100 mb-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">Petani:</span>
                                            <span class="font-semibold text-gray-900 flex items-center gap-1">
                                                <span>👨‍🌾</span>
                                                <span>{{ $period->farmer->name }}</span>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">Pedagang:</span>
                                            <span class="font-semibold text-indigo-700 flex items-center gap-1">
                                                <span>🏪</span>
                                                <span>{{ $period->trader->name }} (Anda)</span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Rincian Kuota & Nominal --}}
                                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                        <div class="bg-indigo-50/50 p-2.5 rounded-lg border border-indigo-100">
                                            <span class="text-gray-500 block text-[11px]">Kapasitas Terkunci:</span>
                                            <span class="font-bold text-indigo-900 text-sm mt-0.5 block">
                                                {{ number_format($period->locked_capacity, 2, ',', '.') }} {{ $period->commodity->unit }}/hari
                                            </span>
                                        </div>
                                        <div class="bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100">
                                            <span class="text-gray-500 block text-[11px]">Total Akumulasi:</span>
                                            <span class="font-bold text-emerald-800 text-sm mt-0.5 block">
                                                Rp {{ number_format($period->total_amount, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Aksi Buka Ruang Rekap --}}
                                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-[11px] text-gray-500">
                                        ID Kontrak #{{ $period->id }}
                                    </span>
                                    <a href="{{ route('rekap.show', $period) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-700 transition shadow-sm gap-1">
                                        Buka Ruang Rekap &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ======================================================== --}}
            {{-- KONTEN TAB: SEBAGAI PETANI (TEMA EMERALD) --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'farmer'" class="space-y-6" style="display: none;">
                {{-- Banner Kontekstual Petani --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3 text-emerald-950">
                    <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-xs leading-relaxed">
                        <span class="font-bold text-sm block mb-0.5 text-emerald-900">Peran Anda: Petani</span>
                        Pada ruang rekap berikut, Anda bertindak sebagai penjual/pemilik hasil panen. Akses Anda bersifat <strong>pemantauan (read-only)</strong> terhadap pencatatan setoran harian oleh pedagang, serta Anda memiliki wewenang untuk <strong>menandai lunas</strong> transaksi saat pembayaran telah diterima.
                    </div>
                </div>

                @if ($farmerRecaps->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Ruang Rekap Sebagai Petani</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Saat Anda menyetujui tawaran kontrak dari pedagang mitra, sistem otomatis membentuk ruang rekapitulasi terpisah di tab ini.
                        </p>
                        <div class="mt-6 flex justify-center gap-3">
                            <a href="{{ route('farmer.offers.index') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-emerald-700 shadow-sm transition">
                                Tinjau Tawaran Masuk &rarr;
                            </a>
                            <a href="{{ route('farmer.commodities.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-gray-50 transition shadow-sm">
                                Kelola Komoditas Saya
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($farmerRecaps as $period)
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between border-t-4 border-t-emerald-600">
                                <div class="p-5">
                                    {{-- Header Kartu: Komoditas & Badge Status --}}
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-base leading-tight">
                                                {{ $period->commodity->name }}
                                            </h3>
                                            <span class="text-xs text-gray-500 font-medium">
                                                Satuan: {{ $period->commodity->unit }}
                                            </span>
                                        </div>

                                        {{-- Badge Status Periode & Pembayaran --}}
                                        <div>
                                            @if ($period->status === 'ready')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800" title="Siap Dimulai">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                                    Siap Dimulai
                                                </span>
                                            @elseif ($period->status === 'active')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800" title="Periode Aktif">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                    Aktif
                                                </span>
                                            @elseif ($period->status === 'closed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800" title="Menunggu Pembayaran">
                                                    Menunggu Pembayaran
                                                </span>
                                            @elseif ($period->status === 'paid')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800" title="Lunas / Selesai">
                                                    <svg class="w-3 h-3 mr-1 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Lunas / Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Identitas Lengkap Pasangan Kontrak --}}
                                    <div class="bg-gray-50 rounded-lg p-3 space-y-1.5 text-xs border border-gray-100 mb-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">Petani:</span>
                                            <span class="font-semibold text-emerald-700 flex items-center gap-1">
                                                <span>👨‍🌾</span>
                                                <span>{{ $period->farmer->name }} (Anda)</span>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">Pedagang:</span>
                                            <span class="font-semibold text-gray-900 flex items-center gap-1">
                                                <span>🏪</span>
                                                <span>{{ $period->trader->name }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Rincian Kuota & Nominal --}}
                                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                        <div class="bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100">
                                            <span class="text-gray-500 block text-[11px]">Kapasitas Terkunci:</span>
                                            <span class="font-bold text-emerald-900 text-sm mt-0.5 block">
                                                {{ number_format($period->locked_capacity, 2, ',', '.') }} {{ $period->commodity->unit }}/hari
                                            </span>
                                        </div>
                                        <div class="bg-indigo-50/50 p-2.5 rounded-lg border border-indigo-100">
                                            <span class="text-gray-500 block text-[11px]">Total Akumulasi:</span>
                                            <span class="font-bold text-indigo-900 text-sm mt-0.5 block">
                                                Rp {{ number_format($period->total_amount, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Aksi Buka Ruang Rekap --}}
                                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-[11px] text-gray-500">
                                        ID Kontrak #{{ $period->id }}
                                    </span>
                                    <a href="{{ route('rekap.show', $period) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white rounded-md text-xs font-semibold hover:bg-emerald-700 transition shadow-sm gap-1">
                                        Buka Ruang Rekap &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
