<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                        Mode Pedagang
                    </span>
                    <span class="text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500">Pemantauan Kontrak Pra-Panen</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Tawaran Saya') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Pantau status penawaran kapasitas dan harga yang telah Anda ajukan kepada petani mitra.
                </p>
            </div>
            <div>
                <a href="{{ route('trader.commodities.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Komoditas Lain
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                @if ($offers->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Tawaran yang Diajukan</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Anda belum mengajukan tawaran pra-panen ke komoditas petani manapun. Kunjungi katalog komoditas untuk memulai kerja sama.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('trader.commodities.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-indigo-700 shadow-sm transition">
                                Eksplorasi Komoditas Sekarang &rarr;
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Komoditas & Petani
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Kapasitas Harian
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Estimasi Harga Acuan
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Tanggal Diajukan
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($offers as $offer)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-6 py-4 whitespace-normal max-w-sm">
                                            <div class="font-bold text-gray-900">
                                                {{ $offer->commodity->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                                <span>Petani: {{ $offer->commodity->farmer->name }}</span>
                                                @if ($offer->commodity->farmer->phone)
                                                    <span>&bull; {{ $offer->commodity->farmer->phone }}</span>
                                                @endif
                                            </div>

                                            {{-- Kotak Informasi Catatan / Alasan dari Petani --}}
                                            @if ($offer->farmer_notes && in_array($offer->status, ['approved', 'rejected']))
                                                @if ($offer->status === 'approved')
                                                    <div class="mt-2.5 p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 shadow-xs">
                                                        <div class="font-semibold text-emerald-800 flex items-center gap-1.5 mb-0.5">
                                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Catatan dari Petani:
                                                        </div>
                                                        <p class="text-emerald-950 mt-1 whitespace-pre-line leading-relaxed">{{ $offer->farmer_notes }}</p>
                                                    </div>
                                                @else
                                                    <div class="mt-2.5 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-900 shadow-xs">
                                                        <div class="font-semibold text-rose-800 flex items-center gap-1.5 mb-0.5">
                                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Alasan Penolakan dari Petani:
                                                        </div>
                                                        <p class="text-rose-950 mt-1 whitespace-pre-line leading-relaxed">{{ $offer->farmer_notes }}</p>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ number_format($offer->offered_daily_capacity, 2, ',', '.') }} {{ $offer->commodity->unit }}/hari
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-emerald-700">
                                            Rp {{ number_format($offer->offered_price, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                            {{ $offer->created_at->translatedFormat('d M Y, H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($offer->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                    Menunggu Persetujuan
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
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                            @if ($offer->status === 'pending')
                                                <form action="{{ route('trader.offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan tawaran ini?');" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-900 font-semibold bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-md transition" title="Batalkan pengajuan tawaran">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
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
