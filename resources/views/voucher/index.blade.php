<x-app-layout :title="'Voucher'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Voucher</h1>
            <a href="{{ route('voucher.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Voucher</a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <form method="GET" action="{{ route('voucher.index') }}">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-md">
                        <label for="search" class="sr-only">Cari voucher</label>
                        <div class="relative flex items-center">
                            <svg class="absolute left-3 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                placeholder="Cari Nama atau Kode voucher..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                            >
                        </div>
                    </div>
                    @if (request('search'))
                        <a href="{{ route('voucher.index') }}" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>
                <p class="mt-2 text-xs text-gray-500">Pencarian berdasarkan: Nama, Kode</p>
            </form>
        </div>

        @if ($vouchers->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    @if (request('search'))
                        Tidak ada voucher ditemukan
                    @else
                        Belum ada voucher
                    @endif
                </h3>
                <p class="text-gray-500 mb-4">
                    @if (request('search'))
                        Coba kata kunci lain atau <a href="{{ route('voucher.index') }}" class="text-blue-600 hover:underline">reset pencarian</a>
                    @else
                        Tambahkan voucher pertama Anda untuk memulai
                    @endif
                </p>
                @if (!request('search'))
                    <a href="{{ route('voucher.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Voucher</a>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba/Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($vouchers as $voucher)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $voucher->nama }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">{{ $voucher->kode }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-900">{{ $voucher->jenis }}</td>
                                <td class="px-6 py-4 text-gray-900">Rp {{ number_format($voucher->harga_modal, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-gray-900">Rp {{ number_format($voucher->harga_jual, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $voucher->hitungStokTersedia() }}</td>
                                <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($voucher->laba_per_unit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $voucher->status === 'aktif' ? 'bg-green-100 text-green-800' : 
                                           ($voucher->status === 'habis' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($voucher->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('voucher.show', $voucher) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Detail</a>
                                        <a href="{{ route('voucher.edit', $voucher) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Edit</a>
                                        <form method="POST" action="{{ route('voucher.destroy', $voucher) }}" class="inline" onsubmit="return confirm('Yakin hapus voucher ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>