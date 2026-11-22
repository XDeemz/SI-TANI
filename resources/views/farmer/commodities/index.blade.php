<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                        Mode Petani
                    </span>
                    <span class="text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500">Manajemen Hasil Panen</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Komoditas Saya') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Kelola daftar komoditas pertanian Anda, pantau sisa kapasitas harian, dan buka/tutup ketersediaan tawaran.
                </p>
            </div>
            <div>
                <a href="{{ route('farmer.commodities.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Komoditas
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

            {{-- Table / List Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                @if ($commodities->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Komoditas Terdaftar</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Anda belum mendaftarkan komoditas hasil tani. Mulai unggah komoditas Anda agar dapat dilihat dan ditawar oleh para pedagang.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('farmer.commodities.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-emerald-700 shadow-sm transition">
                                + Daftarkan Komoditas Pertama
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Komoditas
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Kapasitas Harian
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Est. Masuk Panen
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status Tawaran
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                @foreach ($commodities as $commodity)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="font-bold text-gray-900">
                                                        {{ $commodity->name }}
                                                    </div>
                                                    @if ($commodity->notes)
                                                        <div class="text-xs text-gray-500 mt-0.5 line-clamp-1 max-w-xs" title="{{ $commodity->notes }}">
                                                            {{ $commodity->notes }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-gray-900 font-medium">
                                                <span class="text-emerald-700 font-bold">{{ number_format($commodity->remaining_daily_capacity, 2, ',', '.') }}</span>
                                                <span class="text-gray-400">/</span>
                                                <span>{{ number_format($commodity->initial_daily_capacity, 2, ',', '.') }} {{ $commodity->unit }}/hari</span>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                Sisa Kapasitas Acuan
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {{ \Carbon\Carbon::parse($commodity->estimated_harvest_date)->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex flex-col items-center gap-1.5">
                                                @if ($commodity->status === 'open')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                        Terbuka (Open)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                                        Ditutup (Closed)
                                                    </span>
                                                @endif

                                                <form action="{{ route('farmer.commodities.toggle-status', $commodity) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs underline text-gray-500 hover:text-emerald-600 focus:outline-none transition" title="Ganti status ketersediaan tawaran">
                                                        {{ $commodity->status === 'open' ? 'Tutup Tawaran' : 'Buka Tawaran' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('farmer.commodities.edit', $commodity) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-semibold rounded-md transition" title="Ubah data komoditas">
                                                    Edit
                                                </a>

                                                <form action="{{ route('farmer.commodities.destroy', $commodity) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komoditas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold rounded-md transition" title="Hapus komoditas">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($commodities->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $commodities->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
