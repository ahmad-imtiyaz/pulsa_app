<x-app-layout :title="'Tambah Pengeluaran'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Pengeluaran</h1>
            <a href="{{ route('pengeluaran.index') }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <form method="POST" action="{{ route('pengeluaran.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf

            <div>
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select name="kategori" id="kategori" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="toggleKaryawanField()">
                    <option value="">Pilih kategori</option>
                    <option value="operasional">Operasional</option>
                    <option value="gaji">Gaji Karyawan</option>
                    <option value="pribadi">Pengambilan Pribadi</option>
                </select>
                @error('kategori')
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
                <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah') }}" required min="0" step="1000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 50000">
                @error('jumlah')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="karyawan-field" class="hidden">
                <label for="karyawan_nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Karyawan <span class="text-red-500">*</span></label>
                <input type="text" name="karyawan_nama" id="karyawan_nama" value="{{ old('karyawan_nama') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: Budi Santoso">
                @error('karyawan_nama')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Contoh: Bayar listrik, beli pulsa, keperluan pribadi, dll.">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('pengeluaran.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        function toggleKaryawanField() {
            const kategori = document.getElementById('kategori').value;
            const karyawanField = document.getElementById('karyawan-field');
            const karyawanNama = document.getElementById('karyawan_nama');
            
            if (kategori === 'gaji') {
                karyawanField.classList.remove('hidden');
                karyawanNama.required = true;
            } else {
                karyawanField.classList.add('hidden');
                karyawanNama.required = false;
                karyawanNama.value = '';
            }
        }
    </script>
</x-app-layout>