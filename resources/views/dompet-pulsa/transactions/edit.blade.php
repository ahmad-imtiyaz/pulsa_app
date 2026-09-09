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
                <label for="nominal" class="block text-sm font-medium text-gray-700 mb-1">Harga Modal / Jumlah Topup <span class="text-red-500">*</span></label>
                <input type="number" name="nominal" id="nominal" value="{{ $transaksi->nominal }}" required min="0" step="1000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 50000">
                @error('nominal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fields for Penjualan only -->
            <div id="penjualan-fields" class="hidden space-y-4">
                <div>
                    <label for="harga_jual" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_jual" id="harga_jual" value="{{ $transaksi->harga_jual }}" min="0" step="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: 55000">
                    @error('harga_jual')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Harga jual ke pelanggan (Laba = Harga Jual - Harga Modal)</p>
                </div>
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
        // Initialize fields on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
        });

        function toggleFields() {
            const jenis = document.getElementById('jenis').value;
            const penjualanFields = document.getElementById('penjualan-fields');
            const hargaJual = document.getElementById('harga_jual');
            
            if (jenis === 'penjualan') {
                penjualanFields.classList.remove('hidden');
                hargaJual.required = true;
            } else {
                penjualanFields.classList.add('hidden');
                hargaJual.required = false;
                hargaJual.value = '';
            }
        }
    </script>
</x-app-layout>