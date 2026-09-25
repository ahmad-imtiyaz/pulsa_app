<x-app-layout :title="'Detail Dompet Pulsa'">
    <div class="space-y-6" x-data="{ openAdjustModal: false, openSaldoModal: false }">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $dompetPulsa->nama }} ({{ $dompetPulsa->kode }})</h1>
                <p class="text-gray-500">Detail transaksi dompet pulsa</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('dompet-pulsa.transaksi.create', $dompetPulsa) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Transaksi</a>
                <a href="{{ route('dompet-pulsa.edit', $dompetPulsa) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Edit</a>
                <a href="{{ route('dompet-pulsa.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Kembali ke Daftar</a>
            </div>
        </div>

        <!-- Saldo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Modal Awal</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($modalAwal, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">Tetap, tidak berubah oleh top up</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Saldo Awal (Hari Ini)</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Top Up Hari Ini</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($topupHariIni, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Sisa Saldo Saat Ini</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
                    </div>
                    <button type="button" @click="openSaldoModal = true" class="text-gray-400 hover:text-blue-600" title="Sesuaikan saldo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Penjualan & laba -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Harga Modal Terjual (Hari Ini)</p>
                <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Harga Jual (Hari Ini)</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($hargaJualHariIni, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Laba / Rugi Hari Ini</p>
                <p class="text-2xl font-bold {{ $labaHariIni >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">Rp {{ number_format($labaHariIni, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Laba (Semua Tanggal)</p>
                <p class="text-2xl font-bold {{ $labaTotal >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">Rp {{ number_format($labaTotal, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Adjustments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Penyesuaian Saldo</h2>
                <button type="button" @click="openAdjustModal = true" class="text-sm text-blue-600 hover:text-blue-800">Tambah Penyesuaian</button>
            </div>
            @if($dompetPulsa->adjustments->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada penyesuaian saldo</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($dompetPulsa->adjustments as $adj)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $adj->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $adj->jenis === 'tambah' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $adj->jenis === 'tambah' ? 'Tambah' : 'Kurang' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($adj->nominal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $adj->keterangan ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('dompet-pulsa.adjustments.destroy', [$dompetPulsa, $adj]) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus penyesuaian ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Transaksi</h2>
                <a href="{{ route('dompet-pulsa.transaksi.create', $dompetPulsa) }}" class="text-sm text-blue-600 hover:text-blue-800">Tambah Transaksi</a>
            </div>

            @if ($transactions->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500">Belum ada transaksi</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal / Topup</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($transactions as $tx)
                                @php $laba = $tx->jenis === 'penjualan' ? (float) $tx->harga_jual - (float) $tx->nominal : null; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $tx->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tx->jenis === 'topup' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $tx->jenis === 'topup' ? 'Top Up' : 'Penjualan' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->nominal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-gray-900">
                                        {{ $tx->jenis === 'penjualan' ? 'Rp ' . number_format($tx->harga_jual ?? 0, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium {{ $laba === null ? 'text-gray-400' : ($laba >= 0 ? 'text-green-600' : 'text-red-600') }}">
                                        {{ $laba === null ? '-' : 'Rp ' . number_format($laba, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $tx->keterangan ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('dompet-pulsa.transaksi.edit', [$dompetPulsa, $tx]) }}" class="px-2 py-1 text-xs text-blue-600 hover:text-blue-800 hover:underline">Edit</a>
                                            <form method="POST" action="{{ route('dompet-pulsa.transaksi.destroy', [$dompetPulsa, $tx]) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
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

        <!-- Modal Tambah Penyesuaian -->
        <template x-teleport="body">
            <div x-show="openAdjustModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @keydown.escape.window="openAdjustModal = false">
                <div class="fixed inset-0 bg-black bg-opacity-30" @click="openAdjustModal = false"></div>
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm relative z-10" @click.stop>
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 text-sm">Tambah Penyesuaian Saldo</h3>
                        <button type="button" @click="openAdjustModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('dompet-pulsa.adjustments.store', $dompetPulsa) }}" class="p-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Jenis</label>
                            <select name="jenis" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="tambah">Tambah saldo</option>
                                <option value="kurang">Kurangi saldo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nominal</label>
                            <input type="number" name="nominal" required min="0.01" step="any" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                            <input type="date" name="tanggal" required value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Contoh: selisih saldo aplikasi">
                        </div>
                        <p class="text-xs text-gray-400">Penyesuaian hanya mengubah saldo, tidak mengubah laba.</p>
                        <div class="flex justify-end space-x-2 pt-1 border-t border-gray-100">
                            <button type="button" @click="openAdjustModal = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Modal Override/Set Saldo Target -->
        <template x-teleport="body">
            <div x-show="openSaldoModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @keydown.escape.window="openSaldoModal = false">
                <div class="fixed inset-0 bg-black bg-opacity-30" @click="openSaldoModal = false"></div>
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm relative z-10" @click.stop>
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 text-sm">Sesuaikan Sisa Saldo</h3>
                    </div>
                    <form method="POST" action="{{ route('dompet-pulsa.saldo-override.update', $dompetPulsa) }}" class="p-4 space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Saldo menurut sistem</label>
                            <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-600 font-mono">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Saldo sebenarnya (di aplikasi provider)</label>
                            <input type="number" name="saldo_target" required min="0" step="any"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Contoh: 990700">
                        </div>
                        <p class="text-xs text-gray-400">Selisihnya dicatat sebagai penyesuaian hari ini. Laba tidak berubah.</p>
                        <div class="flex justify-end space-x-2 pt-1 border-t border-gray-100">
                            <button type="button" @click="openSaldoModal = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
