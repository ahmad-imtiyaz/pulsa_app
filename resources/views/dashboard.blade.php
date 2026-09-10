<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <form method="GET" class="flex items-center space-x-3">
                <label for="tanggal" class="text-sm font-medium text-gray-700">Tanggal:</label>
                <input type="date" name="tanggal" id="tanggal" 
                       value="{{ $tanggal->format('Y-m-d') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Lihat</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Saldo Pulsa</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">Rp {{ number_format($data['total_saldo_pulsa'], 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Stok Aksesoris</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $data['total_stok_aksesoris'] }} item</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">
                                Rp {{ number_format(($data['summary']->pulsa_penjualan ?? 0) + ($data['summary']->voucher_penjualan_jual ?? 0) + ($data['summary']->aksesoris_penjualan_jual ?? 0), 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Laba Bersih</p>
                            <p class="text-3xl font-bold {{ $data['summary']->sisa_laba >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                                Rp {{ number_format($data['summary']->sisa_laba ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Dompet Pulsa</h2>
                        <a href="{{ route('dompet-pulsa.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($data['dompets'] as $dompet)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $dompet['nama'] }} ({{ $dompet['kode'] }})</p>
                                        <p class="text-sm text-gray-500">Saldo: Rp {{ number_format($dompet['saldo_akhir'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Penjualan: Rp {{ number_format($dompet['penjualan'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if ($data['dompets']->isEmpty())
                            <p class="text-center text-gray-500 py-4">Belum ada dompet pulsa</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Voucher</h2>
                        <a href="{{ route('voucher.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($data['vouchers'] as $voucher)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $voucher['nama'] }}</p>
                                        <p class="text-sm text-gray-500">Stok: {{ $voucher['stok'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-green-600 font-medium">Laba: Rp {{ number_format($voucher['laba_hari_ini'], 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-500">Terjual: {{ $voucher['terjual_hari_ini'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if ($data['vouchers']->isEmpty())
                            <p class="text-center text-gray-500 py-4">Belum ada voucher</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Aksesoris</h2>
                        <a href="{{ route('aksesoris.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($data['aksesoris'] as $aksesoris)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $aksesoris['nama'] }} ({{ $aksesoris['sku'] }})</p>
                                        <p class="text-sm text-gray-500">Stok: {{ $aksesoris['stok'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-green-600 font-medium">Laba: Rp {{ number_format($aksesoris['laba_hari_ini'], 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-500">Terjual: {{ $aksesoris['terjual_hari_ini'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if ($data['aksesoris']->isEmpty())
                            <p class="text-center text-gray-500 py-4">Belum ada aksesoris</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Keuangan Hari Ini</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Laba Pulsa</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($data['summary']->pulsa_laba ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Laba Voucher</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($data['summary']->voucher_laba ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Laba Aksesoris</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($data['summary']->aksesoris_laba ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-700">Total Laba Kotor</p>
                        <p class="text-xl font-bold text-blue-900">Rp {{ number_format($data['summary']->total_laba_kotor ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Pengeluaran Operasional</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($data['summary']->pengeluaran_operasional ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Gaji Karyawan</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($data['summary']->pengeluaran_gaji ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Pengambilan Pribadi</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($data['summary']->pengeluaran_pribadi ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between p-4 bg-{{ $data['summary']->sisa_laba >= 0 ? 'green' : 'red' }}-50 rounded-lg border border-{{ $data['summary']->sisa_laba >= 0 ? 'green' : 'red' }}-200">
                        <div>
                            <p class="text-sm text-{{ $data['summary']->sisa_laba >= 0 ? 'green' : 'red' }}-700">Sisa Laba Bersih</p>
                            <p class="text-2xl font-bold text-{{ $data['summary']->sisa_laba >= 0 ? 'green' : 'red' }}-900">Rp {{ number_format($data['summary']->sisa_laba ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>