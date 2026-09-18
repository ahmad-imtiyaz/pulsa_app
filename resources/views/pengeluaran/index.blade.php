<x-app-layout :title="'Pengeluaran'">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Pengeluaran</h1>
            <a href="{{ route('pengeluaran.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Pengeluaran</a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" x-data="pengeluaranSearch()">
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-md">
                    <label for="search" class="sr-only">Cari pengeluaran</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        x-model="search"
                        placeholder="Cari Kategori, Keterangan, atau Nama Karyawan..."
                        class="w-full pl-4 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                    >
                    <div x-show="isLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-gray-400" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    </div>
                </div>
                <template x-if="search">
                    <a href="{{ route('pengeluaran.index') }}" @click.prevent="resetSearch()" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset
                    </a>
                </template>
            </div>
            <p class="mt-2 text-xs text-gray-500">Pencarian berdasarkan: Kategori, Keterangan, Nama Karyawan</p>
        </div>

        @if ($pengeluaran->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    @if (request('search'))
                        Tidak ada pengeluaran ditemukan
                    @else
                        Belum ada pengeluaran
                    @endif
                </h3>
                <p class="text-gray-500 mb-4">
                    @if (request('search'))
                        Coba kata kunci lain atau <a href="{{ route('pengeluaran.index') }}" class="text-blue-600 hover:underline">reset pencarian</a>
                    @else
                        Tambahkan pengeluaran pertama Anda untuk memulai
                    @endif
                </p>
                @if (!request('search'))
                    <a href="{{ route('pengeluaran.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Pengeluaran</a>
                @endif
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
                    @include('pengeluaran.partials.table')
                </table>
                @include('pengeluaran.partials.pagination')
            </div>
        @endif
    </div>
</x-app-layout>

<script>
    function pengeluaranSearch() {
        return {
            search: '{{ request('search') }}',
            isLoading: false,
            debounceTimer: null,

            init() {
                this.$watch('search', (value) => {
                    this.debouncedSearch(value);
                });
            },

            debouncedSearch(value) {
                clearTimeout(this.debounceTimer);
                this.isLoading = true;
                
                this.debounceTimer = setTimeout(() => {
                    this.performSearch(value);
                }, 300);
            },

            async performSearch(value) {
                try {
                    const params = new URLSearchParams();
                    if (value) params.set('search', value);
                    
                    const response = await fetch(`{{ route('pengeluaran.index') }}?${params}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });
                    
                    const data = await response.json();
                    
                    document.getElementById('pengeluaran-table-body').innerHTML = data.html;
                    document.getElementById('pengeluaran-pagination').innerHTML = data.pagination;
                } catch (error) {
                    console.error('Search error:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            resetSearch() {
                this.search = '';
                window.location.href = '{{ route('pengeluaran.index') }}';
            }
        }
    }
</script>