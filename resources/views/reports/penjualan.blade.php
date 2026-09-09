<x-app-layout :title="'Laporan Penjualan'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
        
        <!-- Sub-nav -->
        <div class="flex space-x-4">
            <a href="{{ route('laporan.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg">Ringkasan</a>
            <a href="{{ route('laporan.penjualan') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Penjualan</a>
            <a href="{{ route('laporan.stok') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg">Stok</a>
        </div>
        </div>

        <!-- Date filter -->
        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-end space-x-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
        </form>

        <!-- Pulsa Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h2 class="text-lg font-semibold text-gray-900">Penjualan Pulsa</h2>
            </div>
            
            @if ($pulsaSales->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Tidak ada data penjualan pulsa</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dompet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($pulsaSales as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $tx->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $tx->dompetPulsa->nama }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $tx->provider ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->nominal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->harga_jual, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($tx->laba, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Voucher Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                <h2 class="text-lg font-semibold text-gray-900">Penjualan Voucher</h2>
            </div>
            
            @if ($voucherSales->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Tidak ada data penjualan voucher</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($voucherSales as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $tx->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $tx->voucher->nama }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $tx->jumlah }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->harga_modal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->harga_jual, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->total_modal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->total_penjualan, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($tx->laba, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Aksesoris Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-indigo-50">
                <h2 class="text-lg font-semibold text-gray-900">Penjualan Aksesoris</h2>
            </div>
            
            @if ($aksesorisSales->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Tidak ada data penjualan aksesoris</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksesoris</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Modal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($aksesorisSales as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $tx->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $tx->aksesoris->nama }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $tx->jumlah }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->harga_modal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->harga_jual, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->total_modal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->total_penjualan, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($tx->laba, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>