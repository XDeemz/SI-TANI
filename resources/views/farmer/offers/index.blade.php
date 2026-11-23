<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                        Mode Petani
                    </span>
                    <span class="text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500">Peninjauan Kontrak Pra-Panen</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Tawaran Masuk') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Tinjau dan proses penawaran estimasi kuota harian dan harga dari pedagang untuk komoditas Anda.
                </p>
            </div>
            <div>
                <a href="{{ route('farmer.commodities.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Komoditas Saya
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-rose-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Filter Status Tabs --}}
            <div class="flex items-center gap-2 border-b border-gray-200 pb-2 overflow-x-auto">
                <a href="{{ route('farmer.offers.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Semua ({{ $counts['all'] }})
                </a>
                <a href="{{ route('farmer.offers.index', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Menunggu ({{ $counts['pending'] }})
                </a>
                <a href="{{ route('farmer.offers.index', ['status' => 'approved']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Disetujui ({{ $counts['approved'] }})
                </a>
                <a href="{{ route('farmer.offers.index', ['status' => 'rejected']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    Ditolak ({{ $counts['rejected'] }})
                </a>
            </div>

            {{-- Table / Cards Tawaran --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                @if ($offers->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Tidak Ada Tawaran Masuk</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Saat ini belum ada tawaran dari pedagang yang sesuai dengan kriteria filter yang Anda pilih.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Komoditas
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Pedagang
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Kapasitas Diajukan
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Harga Penawaran
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Aksi Keputusan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($offers as $offer)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        {{-- Komoditas --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-gray-900">
                                                {{ $offer->commodity->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                Sisa Kapasitas Saat Ini:
                                                <span class="font-semibold text-emerald-700">
                                                    {{ number_format($offer->commodity->remaining_daily_capacity, 2, ',', '.') }} {{ $offer->commodity->unit }}/hari
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Pedagang --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-gray-900">
                                                {{ $offer->trader->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                @if ($offer->trader->phone)
                                                    <span>📞 {{ $offer->trader->phone }}</span>
                                                @else
                                                    <span>✉️ {{ $offer->trader->email }}</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Kapasitas Diajukan --}}
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ number_format($offer->offered_daily_capacity, 2, ',', '.') }} {{ $offer->commodity->unit }}/hari
                                        </td>

                                        {{-- Harga Penawaran --}}
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-emerald-700">
                                            Rp {{ number_format($offer->offered_price, 2, ',', '.') }}/{{ $offer->commodity->unit }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($offer->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                    Menunggu Keputusan
                                                </span>
                                            @elseif ($offer->status === 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                                    Disetujui
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                                    Ditolak
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Aksi Keputusan --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                            @if ($offer->status === 'pending')
                                                <div class="flex items-center justify-end gap-2">
                                                    {{-- Tombol Terima Tawaran --}}
                                                    <form action="{{ route('farmer.offers.accept', $offer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui tawaran dari {{ $offer->trader->name }}? Kuota harian komoditas akan dikurangi sebesar {{ number_format($offer->offered_daily_capacity, 2, ',', '.') }} {{ $offer->commodity->unit }} dan ruang rekapitulasi akan dibuat.');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-xs font-semibold transition shadow-sm" title="Setujui tawaran dan mulai kontrak">
                                                            Terima Tawaran
                                                        </button>
                                                    </form>

                                                    {{-- Tombol Tolak Tawaran --}}
                                                    <form action="{{ route('farmer.offers.reject', $offer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak tawaran dari {{ $offer->trader->name }}?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-md text-xs font-semibold transition" title="Tolak tawaran">
                                                            Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif ($offer->status === 'approved')
                                                <span class="text-xs font-semibold text-emerald-700">
                                                    Kontrak Aktif
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    Telah Ditolak
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($offers->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $offers->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
