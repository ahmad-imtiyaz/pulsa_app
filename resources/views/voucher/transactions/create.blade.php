<x-app-layout :title="'Tambah Transaksi Voucher'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Transaksi - {{ $voucher->nama }}</h1>
            <a href="{{ route('voucher.show', $voucher) }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <!-- Info Voucher -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-blue-700">Harga Modal</p>
                    <p class="font-medium text-blue-900">Rp {{ number_format($voucher->harga_modal, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-blue-700">Harga Jual</p>
                    <p class="font-medium text-blue-900">Rp {{ number_format($voucher->harga_jual, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-blue-700">Laba/Unit</p>
                    <p class="font-medium text-green-600">Rp {{ number_format($voucher->laba_per_unit, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-blue-700">Stok Tersedia</p>
                    <p class="font-medium {{ $voucher->hitungStokTersedia() > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $voucher->hitungStokTersedia() }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('voucher.transaksi.store', $voucher) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf

            <div>
                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('tanggal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah') }}" required min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 2">
                @error('jumlah')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Stok tersedia: {{ $voucher->hitungStokTersedia() }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="harga_modal" class="block text-sm font-medium text-gray-700 mb-1">Harga Modal <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_modal" id="harga_modal" value="{{ old('harga_modal', $voucher->harga_modal) }}" required min="0" step="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('harga_modal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="harga_jual" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_jual" id="harga_jual" value="{{ old('harga_jual', $voucher->harga_jual) }}" required min="0" step="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('harga_jual')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700">Perhitungan Otomatis:</p>
                <div class="grid grid-cols-3 gap-4 mt-2 text-sm">
                    <div>
                        <p class="text-gray-500">Total Modal</p>
                        <p id="total_modal" class="font-medium text-gray-900">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Jual</p>
                        <p id="total_jual" class="font-medium text-gray-900">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Laba</p>
                        <p id="laba" class="font-medium text-green-600">Rp 0</p>
                    </div>
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('voucher.show', $voucher) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Transaksi</button>
            </div>
        </form>
    </div>

    <script>
        function calculateTotals() {
            const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
            const hargaModal = parseFloat(document.getElementById('harga_modal').value) || 0;
            const hargaJual = parseFloat(document.getElementById('harga_jual').value) || 0;
            
            const totalModal = jumlah * hargaModal;
            const totalJual = jumlah * hargaJual;
            const laba = totalJual - totalModal;
            
            document.getElementById('total_modal').textContent = 'Rp ' + totalModal.toLocaleString('id-ID');
            document.getElementById('total_jual').textContent = 'Rp ' + totalJual.toLocaleString('id-ID');
            document.getElementById('laba').textContent = 'Rp ' + laba.toLocaleString('id-ID');
        }
        
        document.getElementById('jumlah').addEventListener('input', calculateTotals);
        document.getElementById('harga_modal').addEventListener('input', calculateTotals);
        document.getElementById('harga_jual').addEventListener('input', calculateTotals);
        
        calculateTotals();
    </script>
</x-app-layout>