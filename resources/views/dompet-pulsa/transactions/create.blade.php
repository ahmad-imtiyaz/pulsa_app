<x-app-layout :title="'Tambah Transaksi Pulsa'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Transaksi - {{ $dompetPulsa->nama }}</h1>
            <a href="{{ route('dompet-pulsa.show', $dompetPulsa) }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <form method="POST" action="{{ route('dompet-pulsa.transaksi.store', $dompetPulsa) }}"
              x-data="{
                  jenis: '{{ old('jenis') }}',
                  nominal: '{{ old('nominal') }}',
                  hargaJual: '{{ old('harga_jual') }}',
                  get laba() { return (parseFloat(this.hargaJual) || 0) - (parseFloat(this.nominal) || 0); },
                  rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); }
              }"
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf

            <div>
                <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi <span class="text-red-500">*</span></label>
                <select name="jenis" id="jenis" required x-model="jenis"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih jenis transaksi</option>
                    <option value="topup">Top Up (Penambahan Saldo)</option>
                    <option value="penjualan">Penjualan Pulsa</option>
                </select>
                @error('jenis')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('tanggal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nominal" class="block text-sm font-medium text-gray-700 mb-1">
                    <span x-text="jenis === 'penjualan' ? 'Harga Modal (saldo yang terpotong)' : 'Jumlah Topup'"></span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="number" name="nominal" id="nominal" x-model="nominal" required min="0" step="any"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 50000">
                @error('nominal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="jenis === 'penjualan'" x-cloak>
                <label for="harga_jual" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (dibayar customer) <span class="text-red-500">*</span></label>
                <input type="number" name="harga_jual" id="harga_jual" x-model="hargaJual" min="0" step="any"
                       :required="jenis === 'penjualan'" :disabled="jenis !== 'penjualan'"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 51000">
                @error('harga_jual')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-3 p-3 rounded-lg border" :class="laba >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                    <p class="text-xs text-gray-500">Laba transaksi ini (Harga Jual − Harga Modal)</p>
                    <p class="text-lg font-bold" :class="laba >= 0 ? 'text-green-700' : 'text-red-700'" x-text="rp(laba)"></p>
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('dompet-pulsa.show', $dompetPulsa) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</x-app-layout>
