<x-app-layout :title="'Tambah Dompet Pulsa'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Dompet Pulsa</h1>
            <a href="{{ route('dompet-pulsa.index') }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
        </div>

        <form method="POST" action="{{ route('dompet-pulsa.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf

            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Dompet <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: Mobo, Dompul, Digipos, Payafast, DANA">
                @error('nama')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="kode" id="kode" value="{{ old('kode') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: MOBO, DOMPUL, DIGI, PAYA, DANA">
                @error('kode')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="saldo_awal" class="block text-sm font-medium text-gray-700 mb-1">Saldo Awal <span class="text-red-500">*</span></label>
                <input type="number" name="saldo_awal" id="saldo_awal" value="{{ old('saldo_awal') }}" required min="0" step="any"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 1000000">
                @error('saldo_awal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Saldo awal dompet pulsa ini</p>
            </div>

            <div>
                <label for="sisa_saldo_awal" class="block text-sm font-medium text-gray-700 mb-1">Sisa Saldo Saat Ini</label>
                <input type="number" name="sisa_saldo_awal" id="sisa_saldo_awal" value="{{ old('sisa_saldo_awal') }}" min="0" step="any"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: 900000 (nilai ini tetap, tidak berubah dari transaksi)">
                <p class="mt-1 text-sm text-gray-500">Nilai ini diinput sekali di awal dan tetap digunakan untuk perhitungan Laba/Rugi, tidak berubah dari transaksi harian</p>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktif</label>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('dompet-pulsa.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</x-app-layout>