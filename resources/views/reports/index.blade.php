<x-app-layout :title="'Laporan'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h1>
        
        <!-- Sub-nav -->
        <div class="flex space-x-4">
            <a href="{{ route('laporan.index') }}" class="px-4 py-2 {{ request()->routeIs('laporan.index') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg">Ringkasan</a>
            <a href="{{ route('laporan.penjualan') }}" class="px-4 py-2 {{ request()->routeIs('laporan.penjualan') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg">Penjualan</a>
            <a href="{{ route('laporan.stok') }}" class="px-4 py-2 {{ request()->routeIs('laporan.stok') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg">Stok</a>
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

        <!-- Total Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Laba Pulsa</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totals['pulsa_laba'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Laba Voucher</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totals['voucher_laba'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Laba Aksesoris</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totals['aksesoris_laba'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6 bg-blue-50">
                <p class="text-sm text-blue-700">Total Laba Kotor</p>
                <p class="text-2xl font-bold text-blue-900">Rp {{ number_format($totals['total_laba_kotor'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Pengeluaran Operasional</p>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totals['pengeluaran_operasional'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Gaji Karyawan</p>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totals['pengeluaran_gaji'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Pengambilan Pribadi</p>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totals['pengeluaran_pribadi'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border {{ $totals['sisa_laba'] >= 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-6">
                <p class="text-sm {{ $totals['sisa_laba'] >= 0 ? 'text-green-700' : 'text-red-700' }}">Sisa Laba</p>
                <p class="text-2xl font-bold {{ $totals['sisa_laba'] >= 0 ? 'text-green-900' : 'text-red-900' }}">Rp {{ number_format($totals['sisa_laba'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Daily Summary Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Harian</h2>
            </div>
            
            @if ($summaries->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada data</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba Pulsa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba Voucher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba Aksesoris</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba Kotor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Laba</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($summaries as $summary)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $summary->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-green-600">Rp {{ number_format($summary->pulsa_laba, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-green-600">Rp {{ number_format($summary->voucher_laba, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-green-600">Rp {{ number_format($summary->aksesoris_laba, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($summary->total_laba_kotor, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-red-600">Rp {{ number_format($summary->total_pengeluaran, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-medium {{ $summary->sisa_laba >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($summary->sisa_laba, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Per Dompet Pulsa -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Per Dompet Pulsa</h2>
            </div>
            
            @if ($dompets->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada data</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($dompets as $dompet)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $dompet['nama'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $dompet['kode'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $dompet['transaksi_count'] }}</td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($dompet['total_penjualan'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>