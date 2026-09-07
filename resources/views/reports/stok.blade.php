<x-app-layout :title="'Laporan Stok'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Laporan Stok</h1>
        
        <!-- Sub-nav -->
        <div class="flex space-x-4">
            <a href="{{ route('laporan.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg">Ringkasan</a>
            <a href="{{ route('laporan.penjualan') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg">Penjualan</a>
            <a href="{{ route('laporan.stok') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Stok</a>
        </div>
        </div>

        <!-- Dompet Pulsa Stok -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h2 class="text-lg font-semibold text-gray-900">Saldo Dompet Pulsa</h2>
            </div>
            
            @if ($dompets->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada dompet pulsa</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Topup</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Sekarang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($dompets as $dompet)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $dompet['nama'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $dompet['kode'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($dompet['saldo_awal'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-green-600">Rp {{ number_format($dompet['total_topup'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-red-600">Rp {{ number_format($dompet['total_penjualan'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-medium {{ $dompet['saldo_sekarang'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($dompet['saldo_sekarang'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Voucher Stok -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                <h2 class="text-lg font-semibold text-gray-900">Stok Voucher</h2>
            </div>
            
            @if ($vouchers->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada voucher</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sekarang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($vouchers as $voucher)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $voucher['nama'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $voucher['kode'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $voucher['stok_awal'] }}</td>
                                    <td class="px-6 py-4 text-red-600">{{ $voucher['terjual'] }}</td>
                                    <td class="px-6 py-4 font-medium {{ $voucher['stok_sekarang'] > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $voucher['stok_sekarang'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($voucher['harga_modal'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($voucher['harga_jual'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Aksesoris Stok -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-indigo-50">
                <h2 class="text-lg font-semibold text-gray-900">Stok Aksesoris</h2>
            </div>
            
            @if ($aksesoris->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada aksesoris</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembelian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penjualan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sekarang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($aksesoris as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item['nama'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $item['sku'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $item['kategori'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $item['stok_awal'] }}</td>
                                    <td class="px-6 py-4 text-green-600">{{ $item['pembelian'] }}</td>
                                    <td class="px-6 py-4 text-red-600">{{ $item['penjualan'] }}</td>
                                    <td class="px-6 py-4 font-medium {{ $item['stok_sekarang'] > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $item['stok_sekarang'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($item['harga_modal'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>