<x-app-layout :title="'Dompet Pulsa'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Dompet Pulsa</h1>
            <a href="{{ route('dompet-pulsa.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Dompet</a>
        </div>

        @if ($dompets->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada dompet pulsa</h3>
                <p class="text-gray-500 mb-4">Tambahkan dompet pulsa pertama Anda untuk memulai</p>
                <a href="{{ route('dompet-pulsa.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Dompet</a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Awal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Sekarang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Topup</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Laba</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($dompets as $dompet)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $dompet->nama }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">{{ $dompet->kode }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-900">Rp {{ number_format($dompet->saldo_awal, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">Rp {{ number_format($dompet->hitungSaldoTersedia(), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-green-600">Rp {{ number_format($dompet->total_topup, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-red-600">Rp {{ number_format($dompet->total_penjualan_modal, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($dompet->total_laba, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $dompet->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $dompet->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('dompet-pulsa.show', $dompet) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Detail</a>
                                        <a href="{{ route('dompet-pulsa.edit', $dompet) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Edit</a>
                                        <form method="POST" action="{{ route('dompet-pulsa.destroy', $dompet) }}" class="inline" onsubmit="return confirm('Yakin hapus dompet ini?')">
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