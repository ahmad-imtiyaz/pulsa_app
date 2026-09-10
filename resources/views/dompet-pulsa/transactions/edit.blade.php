<x-app-layout :title="'Edit Transaksi Pulsa'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Transaksi - {{ $dompetPulsa->nama }}</h1>
            <a href="{{ route('dompet-pulsa.show', $dompetPulsa) }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <form method="POST" action="{{ route('dompet-pulsa.transaksi.update', [$dompetPulsa, $transaksi]) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi <span class="text-red-500">*</span></label>
                <select name="jenis" id="jenis" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="toggleFields()">
                    <option value="">Pilih jenis transaksi</option>
                    <option value="topup" {{ $transaksi->jenis === 'topup' ? 'selected' : '' }}>Top Up (Penambahan Saldo)</option>
                    <option value="penjualan" {{ $transaksi->jenis === 'penjualan' ? 'selected' : '' }}>Penjualan Pulsa</option>
                </select>
                @error('jenis')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="tanggal" value="{{ $transaksi->tanggal->format('Y-m-d') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('tanggal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nominal" id="nominal-label" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Topup <span class="text-red-500">*</span></label>
                <input type="number" name="nominal" id="nominal" value="{{ $transaksi->nominal }}" required min="0" step="any"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 50000">
                @error('nominal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Catatan tambahan...">{{ $transaksi->keterangan }}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('dompet-pulsa.show', $dompetPulsa) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
        // Initialize label on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNominalLabel();
        });

        function updateNominalLabel() {
            const jenis = document.getElementById('jenis').value;
            const label = document.getElementById('nominal-label');
            
            if (jenis === 'penjualan') {
                label.textContent = 'Penjualan Hari Ini *'; // * added via span
                label.innerHTML = 'Penjualan Hari Ini <span class="text-red-500">*</span>';
            } else {
                label.textContent = 'Jumlah Topup *'; // * added via span
                label.innerHTML = 'Jumlah Topup <span class="text-red-500">*</span>';
            }
        }
    </script>
</x-app-layout>