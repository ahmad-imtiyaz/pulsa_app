<x-app-layout :title="'Aksesoris'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Aksesoris</h1>
            <a href="{{ route('aksesoris.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Aksesoris</a>
        </div>

        @if ($aksesoris->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada aksesoris</h3>
                <p class="text-gray-500 mb-4">Tambahkan aksesoris pertama Anda untuk memulai</p>
                <a href="{{ route('aksesoris.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Aksesoris</a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba/Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($aksesoris as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $item->nama }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded">{{ $item->sku }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-900">{{ $item->kategori ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-900">Rp {{ number_format($item->harga_modal, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-gray-900">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->hitungStokTersedia() }}</td>
                                <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($item->laba_per_unit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('aksesoris.show', $item) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Detail</a>
                                        <a href="{{ route('aksesoris.edit', $item) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Edit</a>
                                        <form method="POST" action="{{ route('aksesoris.destroy', $item) }}" class="inline" onsubmit="return confirm('Yakin hapus aksesoris ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>