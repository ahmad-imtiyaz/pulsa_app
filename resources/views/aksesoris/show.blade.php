<x-app-layout :title="'Detail Aksesoris'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $aksesoris->nama }} ({{ $aksesoris->sku }})</h1>
                <p class="text-gray-500">{{ $aksesoris->kategori ?? 'Tanpa kategori' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('aksesoris.transaksi.create', $aksesoris) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Transaksi</a>
                <a href="{{ route('aksesoris.edit', $aksesoris) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Edit</a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Harga Modal</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($aksesoris->harga_modal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Harga Jual</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($aksesoris->harga_jual, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Laba/Unit</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($aksesoris->laba_per_unit, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Stok Awal</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $aksesoris->stok }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Stok Tersedia</p>
                <p class="text-2xl font-bold {{ $aksesoris->hitungStokTersedia() > 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                    {{ $aksesoris->hitungStokTersedia() }}
                </p>
            </div>
        </div>

        <!-- Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Transaksi</h2>
                <a href="{{ route('aksesoris.transaksi.create', $aksesoris) }}" class="text-sm text-blue-600 hover:text-blue-800">Tambah Transaksi</a>
            </div>
            
            @if ($transactions->isEmpty())
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <p class="text-gray-500">Belum ada transaksi</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($transactions as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $tx->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tx->jenis === 'pembelian' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $tx->jenis === 'pembelian' ? 'Pembelian' : 'Penjualan' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $tx->jumlah }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->harga_modal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">
                                        @if ($tx->harga_jual)
                                            Rp {{ number_format($tx->harga_jual, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->total_modal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">
                                        @if ($tx->total_penjualan)
                                            Rp {{ number_format($tx->total_penjualan, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-medium {{ $tx->laba > 0 ? 'text-green-600' : 'text-gray-900' }}">
                                        Rp {{ number_format($tx->laba, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $tx->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>