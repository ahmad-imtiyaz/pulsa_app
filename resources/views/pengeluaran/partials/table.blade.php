<tbody class="divide-y divide-gray-200" id="pengeluaran-table-body">
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