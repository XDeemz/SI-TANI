<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Dashboard Kontekstual') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola seluruh aktivitas panen dan pemesanan Anda dalam satu akun terpadu.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-3 py-1.5 rounded-full shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-medium">Satu Akun: Peran Ganda Kontekstual</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pengingat Kelengkapan Profil (F-02) --}}
            @if ($profileIncomplete)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="shrink-0 text-amber-500 mt-0.5">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ms-3 flex-1 md:flex md:justify-between md:items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-amber-800">
                                    Informasi Profil Belum Lengkap
                                </h3>
                                <p class="text-sm text-amber-700 mt-1">
                                    @if(empty($user->phone) && empty($user->payment_info))
                                        Nomor telepon/WhatsApp dan informasi rekening/instruksi pembayaran (QRIS) belum diisi. Lengkapi agar proses transaksi rekap dan pembayaran berjalan lancar.
                                    @elseif(empty($user->phone))
                                        Nomor telepon/WhatsApp belum diisi. Lengkapi untuk memudahkan koordinasi setoran hasil panen.
                                    @else
                                        Informasi rekening / pembayaran QRIS belum diisi. Data ini dibutuhkan saat Anda bertindak sebagai Petani penerima pembayaran.
                                    @endif
                                </p>
                            </div>
                            <div class="mt-3 md:mt-0 md:ms-4">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-amber-900 bg-amber-200 hover:bg-amber-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition">
                                    Lengkapi Profil &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Kartu Ringkasan Akun --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Selamat Datang, {{ $user->name }}!
                        </h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Email: <span class="font-medium text-gray-700">{{ $user->email }}</span>
                            @if($user->phone)
                                &bull; Kontak: <span class="font-medium text-gray-700">{{ $user->phone }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Pengaturan Profil
                        </a>
                    </div>
                </div>
            </div>

            {{-- Grid Panel Kontekstual: Petani vs Pedagang --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- PANEL MODE SEBAGAI PETANI --}}
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500/20 flex flex-col justify-between overflow-hidden">
                    <div>
                        {{-- Panel Header --}}
                        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-5 text-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full mb-1">
                                            Mode Sebagai Petani
                                        </span>
                                        <h3 class="font-bold text-lg">Kelola Panen & Penjualan</h3>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-emerald-100 mt-2">
                                Unggah komoditas hasil panen, pantau sisa kapasitas harian, dan setujui tawaran kontrak dari pedagang.
                            </p>
                        </div>

                        {{-- Panel Content --}}
                        <div class="p-6 space-y-4">
                            {{-- Komoditas Saya --}}
                            <div class="p-4 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <span>🌾 Komoditas Saya</span>
                                    </h4>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                        {{ $farmerStats['my_commodities_count'] }} Terdaftar
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Daftar komoditas agrikultur milik Anda beserta status ketersediaan dan sisa kapasitas harian.
                                </p>
                            </div>

                            {{-- Tawaran Masuk --}}
                            <div class="p-4 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <span>📥 Tawaran Masuk</span>
                                    </h4>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                        {{ $farmerStats['pending_offers_count'] }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Penawaran estimasi kapasitas harian dan harga dari pedagang yang membutuhkan persetujuan Anda.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Panel Footer / Info --}}
                    <div class="p-4 bg-emerald-50/50 border-t border-emerald-100 text-xs text-emerald-800 flex items-center justify-between">
                        <span>Fitur aktif saat Anda mengunggah komoditas.</span>
                        <span class="font-semibold text-emerald-700">Modul Petani</span>
                    </div>
                </div>

                {{-- PANEL MODE SEBAGAI PEDAGANG --}}
                <div class="bg-white rounded-xl shadow-sm border-2 border-indigo-500/20 flex flex-col justify-between overflow-hidden">
                    <div>
                        {{-- Panel Header --}}
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 p-5 text-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full mb-1">
                                            Mode Sebagai Pedagang
                                        </span>
                                        <h3 class="font-bold text-lg">Pemesanan & Rekap Harian</h3>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-indigo-100 mt-2">
                                Temukan komoditas terbuka milik petani lain, ajukan tawaran kontrak, dan catat realisasi bobot harian.
                            </p>
                        </div>

                        {{-- Panel Content --}}
                        <div class="p-6 space-y-4">
                            {{-- Cari Komoditas --}}
                            <div class="p-4 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <span>🔍 Cari Komoditas</span>
                                    </h4>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                        {{ $traderStats['available_commodities_count'] }} Tersedia
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Eksplorasi komoditas berstatus buka milik petani lain untuk diajukan penawaran pra-panen.
                                </p>
                            </div>

                            {{-- Tawaran Saya --}}
                            <div class="p-4 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <span>📤 Tawaran Saya</span>
                                    </h4>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        {{ $traderStats['my_offers_count'] }} Diajukan
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Pantau penawaran pra-panen yang telah Anda ajukan kepada para petani mitra.
                                </p>
                            </div>

                            {{-- Rekap Aktif --}}
                            <div class="p-4 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <span>📊 Rekap Aktif</span>
                                    </h4>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-700">
                                        {{ $traderStats['active_recaps_count'] }} Berjalan
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Ruang pencatatan harian bertahap bergaya lembar kerja (*sheet*) untuk kontrak panen yang sedang berlangsung.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Panel Footer / Info --}}
                    <div class="p-4 bg-indigo-50/50 border-t border-indigo-100 text-xs text-indigo-800 flex items-center justify-between">
                        <span>Fitur aktif saat Anda menawar komoditas milik pengguna lain.</span>
                        <span class="font-semibold text-indigo-700">Modul Pedagang</span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
