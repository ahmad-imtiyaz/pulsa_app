<x-app-layout :title="'Pengeluaran'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Pengeluaran</h1>
            <a href="{{ route('pengeluaran.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Pengeluaran</a>
        </div>

        @if ($pengeluaran->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pengeluaran</h3>
                <p class="text-gray-500 mb-4">Tambahkan pengeluaran pertama Anda</p>
                <a href="{{ route('pengeluaran.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Pengeluaran</a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($pengeluaran as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->tanggal->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $item->kategori === 'operasional' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($item->kategori === 'gaji' ? 'bg-purple-100 text-purple-800' : 'bg-pink-100 text-pink-800') }}">
                                        {{ ucfirst($item->kategori === 'pribadi' ? 'Pengambilan Pribadi' : ($item->kategori === 'gaji' ? 'Gaji Karyawan' : 'Operasional')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-red-600">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $item->keterangan ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $item->karyawan_nama ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('pengeluaran.edit', $item) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Edit</a>
                                        <form method="POST" action="{{ route('pengeluaran.destroy', $item) }}" class="inline" onsubmit="return confirm('Yakin hapus pengeluaran ini?')">
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
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pengeluaran->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>