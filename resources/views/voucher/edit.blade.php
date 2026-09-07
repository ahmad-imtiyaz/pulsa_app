<x-app-layout :title="'Edit Voucher'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Voucher</h1>
            <a href="{{ route('voucher.index') }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <form method="POST" action="{{ route('voucher.update', $voucher) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Voucher <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $voucher->nama) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('nama')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="kode" id="kode" value="{{ old('kode', $voucher->kode) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('kode')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis <span class="text-red-500">*</span></label>
                <input type="text" name="jenis" id="jenis" value="{{ old('jenis', $voucher->jenis) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('jenis')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nilai" class="block text-sm font-medium text-gray-700 mb-1">Nilai Voucher <span class="text-red-500">*</span></label>
                    <input type="number" name="nilai" id="nilai" value="{{ old('nilai', $voucher->nilai) }}" required min="0" step="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('nilai')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="harga_modal" class="block text-sm font-medium text-gray-700 mb-1">Harga Modal <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_modal" id="harga_modal" value="{{ old('harga_modal', $voucher->harga_modal) }}" required min="0" step="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('harga_modal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="harga_jual" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                <input type="number" name="harga_jual" id="harga_jual" value="{{ old('harga_jual', $voucher->harga_jual) }}" required min="0" step="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('harga_jual')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Harus >= Harga Modal. Laba per unit = Harga Jual - Harga Modal</p>
            </div>

            <div>
                <label for="stok" class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-500">*</span></label>
                <input type="number" name="stok" id="stok" value="{{ old('stok', $voucher->stok) }}" required min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('stok')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Stok total voucher. Stok tersedia = Stok - Terjual</p>
            </div>

            <div>
                <label for="tanggal_berlaku" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berlaku (Expired)</label>
                <input type="date" name="tanggal_berlaku" id="tanggal_berlaku" value="{{ old('tanggal_berlaku', $voucher->tanggal_berlaku?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="aktif" {{ old('status', $voucher->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status', $voucher->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="habis" {{ old('status', $voucher->status) === 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan', $voucher->keterangan) }}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('voucher.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>