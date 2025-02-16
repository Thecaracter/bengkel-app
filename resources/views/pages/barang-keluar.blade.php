<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">
                Data Barang Keluar
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="barangKeluarHandler()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <!-- Search & Filter -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="w-full md:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" 
                                       x-model="filters.start_date"
                                       @change="loadData()"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="w-full md:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                <input type="date"
                                       x-model="filters.end_date"
                                       @change="loadData()"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div class="flex flex-col md:flex-row gap-4 md:justify-end">
                            <div class="w-full md:w-64">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                                <div class="relative">
                                    <input type="text" 
                                           x-model="search"
                                           @input.debounce.300ms="loadData()"
                                           placeholder="Cari nomor nota atau nama barang..." 
                                           class="w-full px-4 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                                </div>
                            </div>

                            <div class="w-full md:w-auto flex items-end">
                                <button @click="openModal()" 
                                        class="w-full md:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg inline-flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Barang Keluar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Nota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in items.data" :key="item.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(item.tanggal)"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.detail[0].barang_masuk.nomor_nota"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <template x-for="(detail, index) in item.detail" :key="detail.id">
                                                <div :class="{ 'mt-1': index > 0 }">
                                                    <span x-text="detail.barang_masuk.barang.nama"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <template x-for="(detail, index) in item.detail" :key="detail.id">
                                                <div :class="{ 'mt-1': index > 0 }">
                                                    <span :class="detail.tipe === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                          x-text="detail.tipe === 'normal' ? 'Normal' : 'Ecer'"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <template x-for="(detail, index) in item.detail" :key="detail.id">
                                                <div :class="{ 'mt-1': index > 0 }">
                                                    <span x-text="formatNumber(detail.jumlah)"></span>
                                                    <span class="text-gray-600" x-text="detail.tipe === 'ecer' ? 
                                                        (detail.barang_masuk.barang.satuan.nama_ecer || detail.barang_masuk.barang.satuan.nama) : 
                                                        detail.barang_masuk.barang.satuan.nama"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <template x-for="(detail, index) in item.detail" :key="detail.id">
                                                <div :class="{ 'mt-1': index > 0 }">
                                                    <span x-text="formatCurrency(detail.jumlah * detail.harga)"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.keterangan || '-'"></td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-3">
                                                <button @click="cetakStruk(item.id)" class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button @click="confirmDelete(item.id)" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!items.data || items.data.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">Tidak ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="text-sm text-gray-700 w-full md:w-auto text-center md:text-left">
                            Menampilkan <span x-text="items.from || 0"></span> sampai <span x-text="items.to || 0"></span> dari
                            <span x-text="items.total || 0"></span> data
                        </div>
                        <div class="flex justify-center space-x-2 w-full md:w-auto">
                            <template x-for="link in items.links" :key="link.label">
                                <button @click="changePage(link.url)"
                                        :disabled="!link.url"
                                        :class="{'bg-indigo-600 text-white': link.active, 'text-gray-700': !link.active}"
                                        class="px-3 py-1 text-sm rounded-md border disabled:opacity-50 disabled:cursor-not-allowed"
                                        x-html="link.label"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Form Modal -->
                    <div x-show="isModalOpen"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>

                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-2xl sm:w-full">
                                <form @submit.prevent="save" @keydown.enter.prevent class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Barang Keluar</h3>
                                    
                                    <div class="space-y-4">
                                        <!-- Tanggal & Keterangan -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                                <input type="datetime-local"
                                                       x-model="form.tanggal"
                                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <div x-show="errors.tanggal" class="mt-1 text-sm text-red-600" x-text="errors.tanggal"></div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                                                <input type="text"
                                                       x-model="form.keterangan"
                                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <div x-show="errors.keterangan" class="mt-1 text-sm text-red-600" x-text="errors.keterangan"></div>
                                            </div>
                                        </div>

                                        <!-- Search Barang -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Cari Barang</label>
                                            <div class="mt-1 relative">
                                                <input type="text"
                                                       x-model="searchBarang"
                                                       @input.debounce.300ms="searchBarangMasuk()"
                                                       @focus="searchResults.length > 0 && (showSearchResults = true)"
                                                       placeholder="Ketik nama atau scan barcode..."
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                
                                                <!-- Search Results Dropdown -->
                                                <div x-show="showSearchResults && searchResults.length > 0"
                                                     @click.away="showSearchResults = false"
                                                     class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border">
                                                    <ul class="max-h-60 overflow-auto py-1">
                                                        <template x-for="result in searchResults" :key="result.id">
                                                            <li @click="selectBarang(result)"
                                                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                                                <div class="font-medium" x-text="result.nama_barang"></div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span x-text="result.nomor_nota"></span>
                                                                    <span class="mx-1">•</span>
                                                                    <span>Stok: 
                                                                        <span x-text="formatNumber(result.stok)"></span>
                                                                        <span x-text="result.satuan_normal"></span>
                                                                        <template x-if="result.bisa_ecer">
                                                                            (<span x-text="formatNumber(result.stok_ecer)"></span>
                                                                            <span x-text="result.satuan_ecer"></span>)
                                                                        </template>
                                                                        </span>
                                                                </div>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Input Barang -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Tipe</label>
                                                <select x-model="selectedTipe"
                                                        :disabled="!selectedBarang?.bisa_ecer"
                                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="normal">Normal</option>
                                                    <option value="ecer" :disabled="!selectedBarang?.bisa_ecer">Ecer</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Jumlah</label>
                                                <div class="mt-1 flex items-center space-x-2">
                                                    <input type="number"
                                                           step="0.01"
                                                           x-model="selectedJumlah"
                                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <span class="text-gray-600 whitespace-nowrap" x-text="selectedBarang ? 
                                                       (selectedTipe === 'ecer' ? selectedBarang.satuan_ecer : selectedBarang.satuan_normal) : '-'"></span>
                                                </div>
                                            </div>
                                            <div class="flex items-end">
                                                <button type="button" 
                                                        @click="addItem()"
                                                        :disabled="!selectedBarang || !selectedJumlah"
                                                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                                    Tambah Barang
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Display Harga -->
                                        <div x-show="selectedBarang" class="mt-2">
                                            <div class="text-sm text-gray-600">
                                                Harga: <span x-text="formatCurrency(selectedTipe === 'normal' ? selectedBarang.harga_jual : selectedBarang.harga_ecer)"></span>
                                                per <span x-text="selectedTipe === 'normal' ? selectedBarang.satuan_normal : selectedBarang.satuan_ecer"></span>
                                            </div>
                                        </div>

                                        <!-- Daftar Barang -->
                                        <div x-show="form.items.length > 0" class="mt-4">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Daftar Barang:</h4>
                                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Barang</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Jumlah</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tipe</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Subtotal</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <template x-for="(item, index) in form.items" :key="index">
                                                            <tr>
                                                                <td class="px-4 py-2 text-sm" x-text="item.nama_barang"></td>
                                                                <td class="px-4 py-2 text-sm">
                                                                    <span x-text="formatNumber(item.jumlah)"></span>
                                                                    <span class="text-gray-600" x-text="item.satuan"></span>
                                                                </td>
                                                                <td class="px-4 py-2 text-sm">
                                                                    <span :class="item.tipe === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                                          x-text="item.tipe === 'normal' ? 'Normal' : 'Ecer'"></span>
                                                                </td>
                                                                <td class="px-4 py-2 text-sm" x-text="formatCurrency(item.harga * item.jumlah)"></td>
                                                                <td class="px-4 py-2 text-sm">
                                                                    <button type="button" @click="removeItem(index)" 
                                                                            class="text-red-600 hover:text-red-900">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button"
                                                @click="closeModal()"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="submit"
                                                :disabled="form.items.length === 0"
                                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div x-show="isDeleteModalOpen"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeDeleteModal()"></div>

                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Barang Keluar</h3>
                                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus data barang keluar ini?</p>
                                    
                                    <div class="mt-4 flex justify-end space-x-3">
                                        <button type="button"
                                                @click="closeDeleteModal()"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="button"
                                                @click="destroy()"
                                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Struk Modal -->
                    <div x-show="showStrukModal"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeStrukModal()"></div>

                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Struk Barang Keluar</h3>
                                    <div class="space-y-2">
                                      
                                    </div>
                                    
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button"
                                                @click="closeStrukModal()"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Tutup
                                        </button>
                                        <button type="button"
                                                @click="printStruk()"
                                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                            Cetak
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert -->
                    <div x-show="flash.message"
                         class="fixed bottom-0 right-0 m-4 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 border-green-500 px-4 py-3"
                         style="display: none;">
                        <div class="flex items-center justify-between">
                            <p x-text="flash.message" class="text-sm font-medium text-gray-900"></p>
                            <button @click="flash.message = ''" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    @push('scripts')
    <script>
    function barangKeluarHandler() {
    return {
    // State Variables
    search: '',
    searchBarang: '',
    searchResults: [],
    showSearchResults: false,
    selectedBarang: null,
    selectedTipe: 'normal',
    selectedJumlah: '',
    items: {
    data: [],
    links: []
    },
    isModalOpen: false,
    isDeleteModalOpen: false,
    showStrukModal: false,
    selectedId: null,
    strukData: {},
    filters: {
    start_date: '',
    end_date: ''
    },
    form: {
    tanggal: new Date().toISOString().slice(0, 16),
    keterangan: '',
    items: []
    },
    errors: {},
    flash: {
    message: ''
    },
    // Lifecycle Methods
    init() {
            this.loadData();
            // Auto focus ke input search saat modal dibuka
            this.$watch('isModalOpen', (value) => {
                if (value) {
                    setTimeout(() => {
                        this.$refs.searchInput?.focus();
                    }, 100);
                }
            });
        },

        // Data Loading Methods
        async loadData(url = null) {
            try {
                const params = new URLSearchParams({
                    search: this.search || '',
                    start_date: this.filters.start_date || '',
                    end_date: this.filters.end_date || ''
                });

                const response = await fetch(url || `/barang-keluar?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');
                this.items = await response.json();
            } catch (error) {
                console.error('Error:', error);
                this.items = { data: [], links: [] };
            }
        },

        async searchBarangMasuk() {
            if (!this.searchBarang) {
                this.searchResults = [];
                this.showSearchResults = false;
                return;
            }

            try {
                const params = new URLSearchParams({
                    search: this.searchBarang,
                    barcode: this.searchBarang
                });

                const response = await fetch(`/barang-keluar/search-barang?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');
                this.searchResults = await response.json();
                this.showSearchResults = true;

                // Auto select if exact barcode match
                if (this.searchResults.length === 1 && 
                    this.searchResults[0].barcode === this.searchBarang) {
                    this.selectBarang(this.searchResults[0]);
                }
            } catch (error) {
                console.error('Error:', error);
                this.searchResults = [];
                this.showSearchResults = false;
            }
        },

        // Barang Selection Methods
        selectBarang(barang) {
            // Check if item already exists in form items
            const existingItem = this.form.items.find(item => 
                item.barang_masuk_id === barang.id
            );

            if (existingItem) {
                alert('Barang ini sudah ditambahkan ke daftar');
                this.searchBarang = '';
                this.showSearchResults = false;
                return;
            }

            this.selectedBarang = barang;
            this.selectedTipe = barang.bisa_ecer ? 'ecer' : 'normal';
            this.selectedJumlah = '1';
            this.searchBarang = barang.nama_barang;
            this.showSearchResults = false;

            // Auto focus ke input jumlah
            setTimeout(() => {
                this.$refs.jumlahInput?.focus();
                this.$refs.jumlahInput?.select();
            }, 100);
        },

        addItem() {
            if (!this.selectedBarang || !this.selectedJumlah) {
                alert('Pilih barang dan masukkan jumlah terlebih dahulu');
                return;
            }

            // Validate quantity against stock
            const quantity = parseFloat(this.selectedJumlah);
            const stock = this.selectedTipe === 'ecer' ? 
                this.selectedBarang.stok_ecer : 
                this.selectedBarang.stok;

            if (quantity > stock) {
                alert(`Jumlah melebihi stok yang tersedia (${stock})`);
                return;
            }

            const item = {
                barang_masuk_id: this.selectedBarang.id,
                nama_barang: this.selectedBarang.nama_barang,
                jumlah: quantity,
                tipe: this.selectedTipe,
                harga: this.selectedTipe === 'ecer' ? 
                    this.selectedBarang.harga_ecer : 
                    this.selectedBarang.harga_jual,
                satuan: this.selectedTipe === 'ecer' ? 
                    this.selectedBarang.satuan_ecer : 
                    this.selectedBarang.satuan_normal
            };

            // Add item to list
            this.form.items.push(item);

            // Reset input for next scan
            this.searchBarang = '';
            this.selectedBarang = null;
            this.selectedTipe = 'normal';
            this.selectedJumlah = '';
            this.searchResults = [];

            // Focus back to search input
            this.$refs.searchInput?.focus();
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
        },

        // Form Submission Methods
        async save() {
            if (this.form.items.length === 0) {
                alert('Tambahkan minimal satu barang');
                return;
            }

            try {
                const response = await fetch('/barang-keluar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = result.errors;
                        return;
                    }
                    throw new Error(result.error || 'Terjadi kesalahan');
                }

                this.flash.message = result.message;
                setTimeout(() => this.flash.message = '', 3000);

                // Show struk if available
                if (result.struk) {
                    this.strukData = result.struk;
                    this.showStrukModal = true;
                }

                await this.loadData();
                this.closeModal();
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan! Silakan coba lagi.');
            }
        },

        async destroy() {
            try {
                const response = await fetch(`/barang-keluar/${this.selectedId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Terjadi kesalahan');
                }

                this.flash.message = result.message;
                setTimeout(() => this.flash.message = '', 3000);

                await this.loadData();
                this.closeDeleteModal();
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan! Silakan coba lagi.');
            }
        },

        // Modal Methods
        openModal() {
            this.isModalOpen = true;
            this.resetForm();
        },

        closeModal() {
            if (this.form.items.length > 0) {
                if (!confirm('Ada barang yang belum disimpan. Yakin ingin menutup?')) {
                    return;
                }
            }
            this.isModalOpen = false;
            this.resetForm();
        },

        resetForm() {
            this.form = {
                tanggal: new Date().toISOString().slice(0, 16),
                keterangan: '',
                items: []
            };
            this.searchBarang = '';
            this.selectedBarang = null;
            this.selectedTipe = 'normal';
            this.selectedJumlah = '';
            this.searchResults = [];
            this.errors = {};
        },

        closeDeleteModal() {
            this.isDeleteModalOpen = false;
            this.selectedId = null;
        },

        closeStrukModal() {
            this.showStrukModal = false;
            this.strukData = {};
        },

        confirmDelete(id) {
            this.selectedId = id;
            this.isDeleteModalOpen = true;
        },

        // Struk Methods
        async cetakStruk(id) {
    try {
        const response = await fetch(`/barang-keluar/${id}/cetak`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Gagal memuat data struk');
        }
        
        this.strukData = data;
        
        // Pastikan struktur data benar
        this.strukData.items = this.strukData.items || [];
        this.strukData.total_keseluruhan = this.strukData.total_keseluruhan || 'Rp 0,00';

        this.showStrukModal = true;
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Gagal mencetak struk');
    }
},

        printStruk() {
    const strukWindow = window.open('', '_blank');
    strukWindow.document.write(`
        <html>
            <head>
                <title>Struk Barang Keluar</title>
                <style>
                    body { 
                        font-family: monospace; 
                        font-size: 12px;
                        padding: 15px;
                    }
                    .center { text-align: center; }
                    .mt { margin-top: 10px; }
                    .mb { margin-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { 
                        padding: 3px 0; 
                        border-bottom: 1px solid #ddd;
                    }
                    th { text-align: left; }
                    .items-table { margin-top: 10px; }
                    .items-table th, .items-table td { 
                        text-align: left; 
                        padding: 5px;
                    }
                </style>
            </head>
            <body>
                <div class="center">
                    <h3>STRUK BARANG KELUAR</h3>
                    <div class="mb">==================</div>
                </div>
                <table>
                    <tr>
                        <th>Tanggal</th>
                        <td>: ${this.strukData.tanggal}</td>
                    </tr>
                    <tr>
                        <th>No. Nota</th>
                        <td>: ${this.strukData.nomor_nota}</td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.strukData.items.map(item => `
                            <tr>
                                <td>${item.nama_barang}</td>
                                <td>${item.jumlah}</td>
                                <td>${item.tipe}</td>
                                <td>${item.harga}</td>
                                <td>${item.total}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total Keseluruhan</th>
                            <td>${this.strukData.total_keseluruhan}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="center mt">
                    <div>==================</div>
                    <div>Terima Kasih</div>
                </div>
            </body>
        </html>
    `);
    strukWindow.document.close();
    strukWindow.print();
},

        // Utility Methods
        changePage(url) {
            if (url) this.loadData(url);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatNumber(number) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        }
    }
}
</script>
@endpush
</x-app-layout>