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
                            <div class="w-full md:w-auto flex items-end">
                                <button @click="resetFilter()" 
                                        class="w-full md:w-auto px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg inline-flex items-center justify-center">
                                    <i class="fas fa-refresh mr-1"></i>
                                    Hari Ini
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex flex-col md:flex-row gap-4 md:justify-end">
                            <div class="w-full md:w-64">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                                <div class="relative">
                                    <input type="text" 
                                           x-model="search"
                                           @input.debounce.300ms="loadData()"
                                           placeholder="Cari nama barang..." 
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

                    <!-- Info Filter Active -->
                    <div x-show="isFilterActive()" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <span class="text-sm text-blue-700">
                                    <span x-show="isTodayFilter()">Menampilkan penjualan hari ini</span>
                                    <span x-show="!isTodayFilter() && (filters.start_date || filters.end_date)">
                                        Filter aktif: <span x-text="formatDate(filters.start_date)"></span> - <span x-text="formatDate(filters.end_date)"></span>
                                    </span>
                                    <span x-show="search && !filters.start_date && !filters.end_date">
                                        Pencarian: "<span x-text="search"></span>"
                                    </span>
                                </span>
                            </div>
                            <button @click="clearAllFilters()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Lihat Semua Data
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bayar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kembalian</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in items.data" :key="item.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(item.tanggal)"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <template x-for="(detail, index) in item.detail" :key="detail.id">
                                                <div :class="{ 'mt-1': index > 0 }">
                                                    <span x-text="detail.barang_masuk?.barang?.nama || 'Barang tidak ditemukan'"></span>
                                                    <span :class="detail.tipe === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                          class="ml-1 px-1 inline-flex text-xs leading-4 font-semibold rounded"
                                                          x-text="detail.tipe === 'normal' ? 'N' : 'E'"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <template x-for="(detail, index) in item.detail" :key="detail.id">
                                                <div :class="{ 'mt-1': index > 0 }">
                                                    <span x-text="formatNumber(detail.jumlah)"></span>
                                                    <span class="text-gray-600" x-text="detail.tipe === 'ecer' ? 
                                                        (detail.barang_masuk?.barang?.satuan?.nama_ecer || detail.barang_masuk?.barang?.satuan?.nama || '') : 
                                                        (detail.barang_masuk?.barang?.satuan?.nama || '')"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-semibold" x-text="formatCurrency(item.total_harga)"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(item.jumlah_bayar)"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(item.kembalian)"></td>
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
                                    <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        <span x-show="isTodayFilter()">Tidak ada penjualan hari ini</span>
                                        <span x-show="!isTodayFilter()">Tidak ada data</span>
                                    </td>
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

                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
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
                                                       x-ref="searchInput"
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
                                                           x-ref="jumlahInput"
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

                                            <!-- Total dan Payment Section -->
                                            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <div class="text-center">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Belanja</label>
                                                        <div class="text-lg font-bold text-green-600" x-text="formatCurrency(calculateTotal())"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar</label>
                                                        <input type="number"
                                                               step="0.01"
                                                               x-model="form.jumlah_bayar"
                                                               @input="calculateChange()"
                                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        <div x-show="errors.jumlah_bayar" class="mt-1 text-sm text-red-600" x-text="errors.jumlah_bayar"></div>
                                                    </div>
                                                    <div class="text-center">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kembalian</label>
                                                        <div class="text-lg font-bold" 
                                                             :class="calculated_change >= 0 ? 'text-blue-600' : 'text-red-600'"
                                                             x-text="formatCurrency(calculated_change)"></div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Quick Payment Buttons -->
                                                <div class="mt-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pembayaran Cepat:</label>
                                                    <div class="flex flex-wrap gap-2">
                                                        <button type="button" @click="setPayment(calculateTotal())" 
                                                                class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">Pas</button>
                                                        <button type="button" @click="setPayment(50000)" 
                                                                class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">50rb</button>
                                                        <button type="button" @click="setPayment(100000)" 
                                                                class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">100rb</button>
                                                        <button type="button" @click="setPayment(200000)" 
                                                                class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">200rb</button>
                                                        <button type="button" @click="setPayment(500000)" 
                                                                class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">500rb</button>
                                                    </div>
                                                </div>
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
                                                :disabled="form.items.length === 0 || !form.jumlah_bayar || calculated_change < 0"
                                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                            Simpan Transaksi
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

                    <!-- Konfirmasi Print Modal -->
                    <div x-show="showConfirmPrintModal"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeConfirmPrintModal()"></div>

                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <div class="p-6">
                                    <div class="flex items-center justify-center mb-4">
                                        <i class="fas fa-print text-4xl text-green-500 mr-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900">Cetak Struk</h3>
                                    </div>
                                    
                                    <p class="text-center text-gray-600 mb-6">Transaksi berhasil disimpan. Apakah Anda ingin mencetak struk?</p>
                                    
                                    <div class="mt-4 flex justify-center space-x-3">
                                        <button type="button"
                                                @click="closeConfirmPrintModal()"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Tidak
                                        </button>
                                        <button type="button"
                                                @click="printStruk(); closeConfirmPrintModal();"
                                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                            Ya, Cetak Sekarang
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
        calculated_change: 0,
        items: {
            data: [],
            links: []
        },
        isModalOpen: false,
        isDeleteModalOpen: false,
        showConfirmPrintModal: false,
        selectedId: null,
        strukData: {},
        filters: {
            start_date: '', // Kosongkan default
            end_date: ''    // Kosongkan default
        },
        form: {
            tanggal: new Date().toISOString().slice(0, 16),
            keterangan: '',
            jumlah_bayar: '',
            items: []
        },
        errors: {},
        flash: {
            message: ''
        },
        
        // Lifecycle Methods
        init() {
            this.loadData();
            this.$watch('isModalOpen', (value) => {
                if (value) {
                    setTimeout(() => {
                        this.$refs.searchInput?.focus();
                    }, 100);
                }
            });
        },

        // Payment Methods
        calculateTotal() {
            return this.form.items.reduce((total, item) => {
                return total + (item.jumlah * item.harga);
            }, 0);
        },

        calculateChange() {
            const total = this.calculateTotal();
            const bayar = parseFloat(this.form.jumlah_bayar) || 0;
            this.calculated_change = bayar - total;
        },

        setPayment(amount) {
            this.form.jumlah_bayar = amount;
            this.calculateChange();
        },

        // Filter Methods
        resetFilter() {
            const today = new Date().toISOString().slice(0, 10);
            this.filters.start_date = today;
            this.filters.end_date = today;
            this.loadData();
        },

        clearAllFilters() {
            this.filters.start_date = '';
            this.filters.end_date = '';
            this.search = '';
            this.loadData();
        },

        isFilterActive() {
            return this.filters.start_date || this.filters.end_date || this.search;
        },

        isTodayFilter() {
            const today = new Date().toISOString().slice(0, 10);
            return this.filters.start_date === today && this.filters.end_date === today;
        },

        // Data Loading Methods
        async loadData(url = null) {
            try {
                const params = new URLSearchParams({
                    search: this.search || ''
                });

                // Hanya tambahkan filter tanggal jika memang ada nilai
                if (this.filters.start_date) {
                    params.append('start_date', this.filters.start_date);
                }
                if (this.filters.end_date) {
                    params.append('end_date', this.filters.end_date);
                }

                // Add reset_filter parameter jika tidak ada filter tanggal
                if (!this.filters.start_date && !this.filters.end_date) {
                    params.append('reset_filter', '1');
                }

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

        // Form Submission Methods
        async save() {
            if (this.form.items.length === 0) {
                alert('Tambahkan minimal satu barang');
                return;
            }

            if (!this.form.jumlah_bayar || this.calculated_change < 0) {
                alert('Jumlah bayar tidak mencukupi');
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

                // Set flash message
                this.flash.message = result.message;
                setTimeout(() => this.flash.message = '', 3000);

                // Jika ada data struk langsung, gunakan itu
                if (result.struk) {
                    this.strukData = result.struk;
                    
                    // Close form modal
                    this.closeModal();
                    
                    // Reload data
                    await this.loadData();
                    
                    // Show print confirmation modal
                    setTimeout(() => {
                        this.showConfirmPrintModal = true;
                    }, 100);
                } else {
                    this.closeModal();
                    await this.loadData();
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan! Silakan coba lagi.');
            }
        },

        // Struk Methods
        async loadStrukData(id) {
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
                this.strukData.items = this.strukData.items || [];
                this.strukData.total_keseluruhan = this.strukData.total_keseluruhan || 'Rp 0,00';
                this.strukData.jumlah_bayar = this.strukData.jumlah_bayar || 'Rp 0,00';
                this.strukData.kembalian = this.strukData.kembalian || 'Rp 0,00';
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Gagal memuat data struk');
            }
        },

        async cetakStruk(id) {
            try {
                await this.loadStrukData(id);
                this.printStruk();
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Gagal mencetak struk');
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

        closeConfirmPrintModal() {
            this.showConfirmPrintModal = false;
            this.selectedId = null;
        },

        resetForm() {
            this.form = {
                tanggal: new Date().toISOString().slice(0, 16),
                keterangan: '',
                jumlah_bayar: '',
                items: []
            };
            this.searchBarang = '';
            this.selectedBarang = null;
            this.selectedTipe = 'normal';
            this.selectedJumlah = '';
            this.calculated_change = 0;
            this.searchResults = [];
            this.errors = {};
        },

        // Item Management Methods
        selectBarang(barang) {
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

            this.form.items.push(item);
            this.calculateChange();
            this.searchBarang = '';
            this.selectedBarang = null;
            this.selectedTipe = 'normal';
            this.selectedJumlah = '';
            this.searchResults = [];
            this.$refs.searchInput?.focus();
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
            this.calculateChange();
        },

        confirmDelete(id) {
            this.selectedId = id;
            this.isDeleteModalOpen = true;
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

        closeDeleteModal() {
            this.isDeleteModalOpen = false;
            this.selectedId = null;
        },

        printStruk() {
            const strukWindow = window.open('', '_blank');
            strukWindow.document.write(`
                <html>
                    <head>
                        <title>Struk Barang Keluar</title>
                        <style>
                            @page {
                                size: 58mm auto;
                                margin: 2mm;
                            }
                            body { 
                                font-family: 'Courier New', monospace; 
                                font-size: 12px;
                                line-height: 1.2;
                                width: 54mm;
                                margin: 0 auto;
                                padding: 2mm;
                                color: #000;
                                background: white;
                            }
                            
                            .center { 
                                text-align: center; 
                                margin: 2px 0;
                            }
                            
                            .header { 
                                font-weight: bold; 
                                font-size: 14px;
                                margin: 3px 0;
                            }
                            
                            .logo { 
                                width: 45mm; 
                                height: auto; 
                                margin: 2px auto;
                                display: block;
                                max-width: 100%;
                            }
                            
                            .divider { 
                                border-bottom: 1px dashed #000; 
                                margin: 4px 0; 
                                height: 1px;
                            }
                            
                            .bold-divider { 
                                border-bottom: 2px solid #000; 
                                margin: 4px 0; 
                                height: 2px;
                            }
                            
                            .store-info {
                                font-size: 10px;
                                margin: 2px 0;
                                line-height: 1.1;
                            }
                            
                            .transaction-info {
                                font-size: 10px;
                                margin: 2px 0;
                                text-align: left;
                            }
                            
                            .items-section {
                                margin: 4px 0;
                            }
                            
                            .item-row {
                                margin: 2px 0;
                                font-size: 11px;
                                display: block;
                                word-wrap: break-word;
                            }
                            
                            .item-name {
                                font-weight: bold;
                                display: block;
                                margin-bottom: 1px;
                            }
                            
                            .item-details {
                                display: flex;
                                justify-content: space-between;
                                font-size: 10px;
                                margin-bottom: 2px;
                            }
                            
                            .item-qty-price {
                                text-align: left;
                            }
                            
                            .item-total {
                                text-align: right;
                                font-weight: bold;
                            }
                            
                            .payment-section {
                                margin-top: 4px;
                                padding-top: 2px;
                            }
                            
                            .payment-row {
                                display: flex;
                                justify-content: space-between;
                                font-size: 11px;
                                margin: 2px 0;
                            }
                            
                            .total-row {
                                display: flex;
                                justify-content: space-between;
                                font-weight: bold;
                                font-size: 13px;
                                margin: 3px 0;
                                padding: 2px 0;
                            }
                            
                            .footer-info {
                                margin-top: 6px;
                                font-size: 9px;
                                line-height: 1.2;
                            }
                            
                            .thanks {
                                margin-top: 6px;
                                font-style: italic;
                                font-size: 10px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="center">
                            <img src="/logo.jpg" alt="Logo Toko" class="logo" onerror="this.style.display='none'">
                        </div>
                        
                        <div class="center store-info">
                            <div style="font-weight: bold; font-size: 12px;">TOKO SWALAYAN</div>
                            <div>Jl. Contoh No. 123</div>
                            <div>Telp: (021) 1234-5678</div>
                        </div>
                        
                        <div class="bold-divider"></div>
                        
                        <div class="center header">NOTA PENJUALAN</div>
                        
                        <div class="divider"></div>
                        
                        <div class="transaction-info">
                            <div>Tanggal: ${this.strukData.tanggal || '-'}</div>
                            <div>Kasir: Admin</div>
                            ${this.strukData.keterangan ? `<div>Ket: ${this.strukData.keterangan}</div>` : ''}
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="items-section">
                            ${this.strukData.items && this.strukData.items.length > 0 ? 
                                this.strukData.items.map((item, index) => `
                                    <div class="item-row">
                                        <div class="item-name">${item.nama_barang || 'Barang'}</div>
                                        <div class="item-details">
                                            <div class="item-qty-price">
                                                ${item.jumlah || '0'} x ${item.harga || 'Rp 0'}
                                            </div>
                                            <div class="item-total">${item.total || 'Rp 0'}</div>
                                        </div>
                                        ${index < this.strukData.items.length - 1 ? '<div class="divider"></div>' : ''}
                                    </div>
                                `).join('') 
                                : '<div class="item-row">Tidak ada item</div>'
                            }
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="payment-section">
                            <div class="total-row">
                                <div>TOTAL:</div>
                                <div>${this.strukData.total_keseluruhan || 'Rp 0,00'}</div>
                            </div>
                            <div class="payment-row">
                                <div>Bayar:</div>
                                <div>${this.strukData.jumlah_bayar || 'Rp 0,00'}</div>
                            </div>
                            <div class="payment-row">
                                <div>Kembalian:</div>
                                <div>${this.strukData.kembalian || 'Rp 0,00'}</div>
                            </div>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="center footer-info">
                            <div>*** TERIMA KASIH ***</div>
                            <div>Barang yang sudah dibeli</div>
                            <div>tidak dapat dikembalikan</div>
                            <div style="font-size: 8px;">Printed: ${new Date().toLocaleString('id-ID')}</div>
                        </div>
                        
                        <div class="center thanks">
                            Selamat Berbelanja Kembali
                        </div>
                        
                        <div style="height: 10mm;"></div>
                    </body>
                </html>
            `);
            
            strukWindow.document.close();
            
            setTimeout(() => {
                strukWindow.print();
                setTimeout(() => {
                    strukWindow.close();
                }, 1000);
            }, 500);
        },

        // Utility Methods
        changePage(url) {
            if (url) this.loadData(url);
        },
        
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('id-ID', {
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