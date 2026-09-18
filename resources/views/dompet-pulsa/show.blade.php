<x-app-layout :title="'Detail Dompet Pulsa'">
    <div class="space-y-6" x-data="{ openSaldoOverrideModal: false }">
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

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Modal Awal</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($modalAwal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Selisih</p>
                <p class="text-2xl font-bold {{ $selisih >= 0 ? 'text-blue-600' : 'text-red-600' }} mt-1">Rp {{ number_format($selisih, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Sisa Saldo Saat Ini</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($sisaSaldoEfektif, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1">Formula: Rp {{ number_format($sisaSaldoDisesuaikan, 0, ',', '.') }}{{ ($dompetPulsa->saldo_delta ?? 0) != 0 ? ' (delta: ' . (($dompetPulsa->saldo_delta ?? 0) >= 0 ? '+' : '') . number_format($dompetPulsa->saldo_delta ?? 0, 0, ',', '.') . ')' : '' }}</p>
                    </div>
                    <button @click="openSaldoOverrideModal = true" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Sisa Saldo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Laba / Rugi</p>
                <p class="text-2xl font-bold {{ $labaRugi >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">Rp {{ number_format($labaRugi, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Additional Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Saldo Awal (Hari Ini)</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Topup Hari Ini</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($topupHariIni, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Adjustments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Penyesuaian Saldo</h2>
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
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <p class="text-gray-500">Belum ada transaksi</p>
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
                            @foreach ($transactions as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $tx->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tx->jenis === 'topup' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $tx->jenis === 'topup' ? 'Top Up' : 'Penjualan' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-900">Rp {{ number_format($tx->nominal, 0, ',', '.') }}</td>
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

        <!-- Saldo Delta Modal -->
        <template x-teleport="body">
        <div
    x-show="openSaldoOverrideModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="openSaldoOverrideModal = false"
>
 <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black bg-opacity-30 transition-opacity" @click="openSaldoOverrideModal = false"></div>
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-xs relative z-10" @click.stop>
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 text-sm">Edit Sisa Saldo</h3>
                        <button @click="openSaldoOverrideModal = false" class="text-gray-400 hover:text-gray-600 w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('dompet-pulsa.saldo-override.update', $dompetPulsa) }}" class="p-4 space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nilai Formula (tidak bisa diubah)</label>
                            <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-600 font-mono">
                                Rp {{ number_format($sisaSaldoDisesuaikan, 0, ',', '.') }}
                            </div>
                        </div>
                        <div x-data="{
    rawValue: {{ $sisaSaldoDisesuaikan + ($dompetPulsa->saldo_delta ?? 0) }},
    get formatted() {
        return this.rawValue
            ? new Intl.NumberFormat('id-ID').format(this.rawValue)
            : '';
    },
    updateValue(e) {
        let digits = e.target.value.replace(/\D/g, '');
        this.rawValue = digits ? parseInt(digits, 10) : 0;
        e.target.value = this.formatted;
    }
}">
                            <label for="saldo_target" class="block text-xs font-medium text-gray-500 mb-1">Nilai Target (akan disimpan sebagai selisih dari formula)</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="saldo_target"
                                    inputmode="numeric"
                                    x-model="formatted"
                                    @input="updateValue"
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 1.500.000">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Selisih saat ini: 
                                <span class="{{ ($dompetPulsa->saldo_delta ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-mono">
                                    {{ ($dompetPulsa->saldo_delta ?? 0) >= 0 ? '+' : '' }}{{ number_format($dompetPulsa->saldo_delta ?? 0, 0, ',', '.') }}
                                </span>
                                dari formula
                            </p>
                            <input type="hidden" name="saldo_delta" :value="rawValue">
                        </div>
                        <div class="flex justify-end space-x-2 pt-1 border-t border-gray-100">
                            <button type="button" @click="openSaldoOverrideModal = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </template>
    </div>
</x-app-layout>
