<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                        Mode Pedagang
                    </span>
                    <span class="text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500">Eksplorasi Pasar Panen Kontrak</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Cari & Eksplorasi Komoditas') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Temukan komoditas pertanian terbuka milik petani lain untuk diajukan penawaran kontrak pra-panen.
                </p>
            </div>
            <div>
                <a href="{{ route('trader.offers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Tawaran Saya
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

            {{-- Filter & Search Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                <form method="GET" action="{{ route('trader.commodities.index') }}" class="space-y-4 md:space-y-0 md:flex md:items-center md:gap-4">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama komoditas, lokasi, atau deskripsi..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="available_only" value="1" {{ request()->boolean('available_only') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2">Hanya kapasitas tersedia (&gt; 0)</span>
                        </label>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-sm">
                            Cari
                        </button>

                        @if(request()->hasAny(['search', 'available_only']))
                            <a href="{{ route('trader.commodities.index') }}" class="text-xs text-gray-500 hover:text-indigo-600 underline">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Grid Komoditas --}}
            @if ($commodities->isEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Tidak Ada Komoditas Ditemukan</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                        Saat ini belum ada komoditas terbuka milik petani lain yang sesuai dengan kriteria pencarian Anda.
                    </p>
                    @if(request()->hasAny(['search', 'available_only']))
                        <div class="mt-4">
                            <a href="{{ route('trader.commodities.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline">
                                Lihat Seluruh Komoditas Terbuka
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($commodities as $commodity)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:border-indigo-300 hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <h3 class="font-bold text-lg text-gray-900 line-clamp-1" title="{{ $commodity->name }}">
                                        {{ $commodity->name }}
                                    </h3>
                                    @if ($commodity->remaining_daily_capacity > 0)
                                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Tersedia
                                        </span>
                                    @else
                                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            Kapasitas Penuh
                                        </span>
                                    @endif
                                </div>

                                {{-- Informasi Petani --}}
                                <div class="text-xs text-gray-500 mb-4 pb-3 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>Petani: <span class="font-semibold text-gray-700">{{ $commodity->farmer->name }}</span></span>
                                    </div>
                                    @if ($commodity->farmer->phone)
                                        <span class="text-gray-400 text-xs" title="Kontak Petani">
                                            📞 {{ $commodity->farmer->phone }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Ringkasan Kapasitas & Tanggal Panen --}}
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-500 text-xs">Sisa Kapasitas Harian:</span>
                                        <span class="font-bold text-indigo-700">
                                            {{ number_format($commodity->remaining_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>Kapasitas Awal:</span>
                                        <span>{{ number_format($commodity->initial_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-50 text-xs">
                                        <span class="text-gray-500">Est. Masuk Panen:</span>
                                        <span class="font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($commodity->estimated_harvest_date)->translatedFormat('d M Y') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Catatan Komoditas --}}
                                @if ($commodity->notes)
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-600 line-clamp-2" title="{{ $commodity->notes }}">
                                        {{ $commodity->notes }}
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Card --}}
                            <div class="p-4 bg-gray-50/70 border-t border-gray-100 flex items-center justify-end">
                                <a href="{{ route('trader.commodities.show', $commodity) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-xs font-semibold transition shadow-sm">
                                    Ajukan Tawaran &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($commodities->hasPages())
                    <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
                        {{ $commodities->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
