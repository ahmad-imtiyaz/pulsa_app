<x-app-layout :title="'Aksesoris'">
    <div
    class="space-y-6"
    x-data="{
        search: @js(request('search')),

        async liveSearch() {
            const url = new URL('{{ route('aksesoris.index') }}', window.location.origin);

            if (this.search.trim()) {
                url.searchParams.set('search', this.search.trim());
            }

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const html = await response.text();

            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newList = doc.querySelector('[data-aksesoris-list]');
            const currentList = document.querySelector('[data-aksesoris-list]');

            if (newList && currentList) {
                currentList.innerHTML = newList.innerHTML;
            }

            window.history.replaceState({}, '', url);
        }
    }"
>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Aksesoris</h1>
            <a href="{{ route('aksesoris.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Aksesoris</a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <form method="GET" action="{{ route('aksesoris.index') }}">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-md">
                        <label for="search" class="sr-only">Cari aksesoris</label>
                        <input
    type="text"
    name="search"
    id="search"
    x-model="search"
    @input.debounce.300ms="liveSearch()"
    placeholder="Cari Nama atau SKU aksesoris..."
    class="w-full pl-4 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
>
                    </div>
                    @if (request('search'))
                        <a href="{{ route('aksesoris.index') }}" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>
                <p class="mt-2 text-xs text-gray-500">Pencarian berdasarkan: Nama, SKU</p>
            </form>
        </div>

        <div data-aksesoris-list>

    @if ($aksesoris->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    @if (request('search'))
                        Tidak ada aksesoris ditemukan
                    @else
                        Belum ada aksesoris
                    @endif
                </h3>
                <p class="text-gray-500 mb-4">
                    @if (request('search'))
                        Coba kata kunci lain atau <a href="{{ route('aksesoris.index') }}" class="text-blue-600 hover:underline">reset pencarian</a>
                    @else
                        Tambahkan aksesoris pertama Anda untuk memulai
                    @endif
                </p>
                @if (!request('search'))
                    <a href="{{ route('aksesoris.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Aksesoris</a>
                @endif
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
    </div>
</x-app-layout>
