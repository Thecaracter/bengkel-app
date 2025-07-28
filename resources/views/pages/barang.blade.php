<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Data Barang</h2>
    </x-slot>
 
    <div class="py-12" x-data="barangHandler()" x-init="loadData()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <!-- Search & Add Button -->
                    <div class="mb-6 flex justify-between">
                        <div class="w-64 relative">
                            <input type="text" 
                                   x-model="search" 
                                   @input.debounce.300ms="loadData()"
                                   placeholder="Cari barang..." 
                                   class="w-full px-4 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
 
                        <button @click="isModalOpen = true; mode = 'create'; resetForm()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Barang
                        </button>
                    </div>
 
                    <!-- Summary Cards -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <i class="fas fa-boxes text-blue-600 text-2xl mr-3"></i>
                                <div>
                                    <div class="text-sm text-blue-600">Total Barang</div>
                                    <div class="text-xl font-bold text-blue-800" x-text="items.length"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
                                <div>
                                    <div class="text-sm text-red-600">Stok Rendah</div>
                                    <div class="text-xl font-bold text-red-800" x-text="items.filter(item => item.stok <= item.stok_minimal).length"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <div class="text-sm text-green-600">Stok Aman</div>
                                    <div class="text-xl font-bold text-green-800" x-text="items.filter(item => item.stok > item.stok_minimal).length"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <div class="flex items-center">
                                <i class="fas fa-star text-purple-600 text-2xl mr-3"></i>
                                <div>
                                    <div class="text-sm text-purple-600">Bisa Ecer</div>
                                    <div class="text-xl font-bold text-purple-800" x-text="items.filter(item => item.bisa_ecer).length"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Saat Ini</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Minimal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in items" :key="item.id">
                                    <tr class="hover:bg-gray-50" :class="item.stok <= item.stok_minimal ? 'bg-red-50 border-l-4 border-red-500' : ''">
                                        <td class="px-6 py-4 text-sm text-gray-600" x-text="item.barcode || '-'"></td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="item.nama"></div>
                                            <div class="text-xs text-gray-500" x-show="item.bisa_ecer">
                                                <i class="fas fa-cut mr-1"></i>Bisa dijual ecer
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.kategori.nama"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.satuan.nama"></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <span :class="item.stok <= item.stok_minimal ? 'text-red-600 font-bold' : 'text-green-600 font-medium'" 
                                                      x-text="item.stok"></span>
                                                <span x-show="item.stok <= item.stok_minimal" class="ml-2">
                                                    <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.stok_minimal"></td>
                                        <td class="px-6 py-4">
                                            <span x-show="item.stok <= item.stok_minimal" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Stok Rendah
                                            </span>
                                            <span x-show="item.stok > item.stok_minimal" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Stok Aman
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col space-y-1">
                                                <button @click="openRestockModal(item)" 
                                                        class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
                                                        title="Tambah Stok">
                                                    <i class="fas fa-plus-circle mr-1"></i>Tambah Stok
                                                </button>
                                                <div class="flex space-x-1">
                                                    <button @click="viewStock(item)" 
                                                            class="inline-flex items-center px-2 py-1 text-xs text-green-700 hover:text-green-900"
                                                            title="Lihat Riwayat Stok">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                    <button @click="edit(item)" 
                                                            class="inline-flex items-center px-2 py-1 text-xs text-indigo-700 hover:text-indigo-900"
                                                            title="Edit Barang">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button @click="confirmDelete(item.id)" 
                                                            class="inline-flex items-center px-2 py-1 text-xs text-red-700 hover:text-red-900"
                                                            title="Hapus Barang">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                                            <p class="text-sm text-gray-500">Belum ada data barang</p>
                                            <button @click="isModalOpen = true; mode = 'create'; resetForm()" 
                                                    class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">
                                                Tambah barang pertama
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Restock Modal -->
                    <div x-show="isRestockModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeRestockModal()"></div>
 
                            <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <form @submit.prevent="saveRestock" class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                                            Tambah Stok Barang
                                        </h3>
                                        <button type="button" @click="closeRestockModal()" class="text-gray-400 hover:text-gray-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Info Barang -->
                                    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                                    <i class="fas fa-box text-blue-600 text-xl"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-blue-900" x-text="selectedItem?.nama"></div>
                                                    <div class="text-sm text-blue-700">
                                                        <i class="fas fa-tag mr-1"></i><span x-text="selectedItem?.kategori?.nama"></span> | 
                                                        <i class="fas fa-ruler mr-1"></i><span x-text="selectedItem?.satuan?.nama"></span>
                                                    </div>
                                                    <div class="text-xs text-blue-600 mt-1" x-show="selectedItem?.barcode">
                                                        <i class="fas fa-barcode mr-1"></i><span x-text="selectedItem?.barcode"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-blue-700">Stok Saat Ini:</div>
                                                <div class="text-2xl font-bold" 
                                                     :class="selectedItem?.stok <= selectedItem?.stok_minimal ? 'text-red-600' : 'text-blue-900'"
                                                     x-text="selectedItem?.stok"></div>
                                                <div class="text-xs text-blue-600">
                                                    Min: <span x-text="selectedItem?.stok_minimal"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Warning untuk stok rendah -->
                                        <div x-show="selectedItem?.stok <= selectedItem?.stok_minimal" 
                                             class="mt-3 p-2 bg-red-100 border border-red-300 rounded-md">
                                            <div class="flex items-center text-red-700">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <span class="text-sm font-medium">Peringatan: Stok barang ini sudah rendah!</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nomor Nota</label>
                                                <input type="text" 
                                                       x-model="restockForm.nomor_nota" 
                                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50"
                                                       readonly>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                                <input type="date" 
                                                       x-model="restockForm.tanggal" 
                                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <div x-show="restockErrors.tanggal" class="mt-1 text-sm text-red-600" x-text="restockErrors.tanggal"></div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Jumlah Stok Masuk *</label>
                                            <input type="number" 
                                                   step="0.01" 
                                                   x-model="restockForm.stok" 
                                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="Masukkan jumlah stok yang akan ditambahkan">
                                            <div x-show="restockErrors.stok" class="mt-1 text-sm text-red-600" x-text="restockErrors.stok"></div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Harga Beli *</label>
                                                <input type="number" 
                                                       x-model="restockForm.harga_beli" 
                                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                       placeholder="0">
                                                <div x-show="restockErrors.harga_beli" class="mt-1 text-sm text-red-600" x-text="restockErrors.harga_beli"></div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Harga Jual *</label>
                                                <input type="number" 
                                                       x-model="restockForm.harga_jual" 
                                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                       placeholder="0">
                                                <div x-show="restockErrors.harga_jual" class="mt-1 text-sm text-red-600" x-text="restockErrors.harga_jual"></div>
                                            </div>
                                        </div>

                                        <div x-show="selectedItem?.bisa_ecer">
                                            <label class="block text-sm font-medium text-gray-700">Harga Ecer</label>
                                            <input type="number" 
                                                   x-model="restockForm.harga_ecer" 
                                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="Kosongkan jika tidak dijual ecer">
                                            <div x-show="restockErrors.harga_ecer" class="mt-1 text-sm text-red-600" x-text="restockErrors.harga_ecer"></div>
                                        </div>

                                        <!-- Preview Stok Baru -->
                                        <div x-show="restockForm.stok" class="p-3 bg-green-50 rounded-lg border border-green-200">
                                            <div class="text-sm text-green-700">
                                                Stok setelah penambahan: 
                                                <span class="font-bold text-green-900">
                                                    <span x-text="(parseFloat(selectedItem?.stok || 0) + parseFloat(restockForm.stok || 0)).toFixed(2)"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
 
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button" @click="closeRestockModal()" 
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="submit" 
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-save mr-2"></i>
                                            Tambah Stok
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
 
                    <!-- Stock History Modal -->
                    <div x-show="isStockModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeStockModal()"></div>
 
                            <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <i class="fas fa-history text-green-600 mr-2"></i>
                                            Riwayat Stok: <span x-text="selectedItem?.nama" class="font-bold"></span>
                                        </h3>
                                        <button @click="closeStockModal()" class="text-gray-400 hover:text-gray-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Nota</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Masuk</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Tersisa</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Ecer</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="stock in stocks" :key="stock.id">
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(stock.tanggal)"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900 font-medium" x-text="stock.nomor_nota"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.jumlah"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.stok"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(stock.harga_beli)"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(stock.harga_jual)"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.harga_ecer ? formatCurrency(stock.harga_ecer) : '-'"></td>
                                                        <td class="px-6 py-4">
                                                            <button @click="confirmDeleteStock(stock)" 
                                                                    class="text-red-600 hover:text-red-900"
                                                                    title="Hapus record stok ini">
                                                                <i class="fas fa-trash text-sm"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <tr x-show="stocks.length === 0">
                                                    <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">Belum ada riwayat stok</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Modal Form Edit Barang -->
                    <div x-show="isModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto" 
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>
 
                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <form @submit.prevent="save" class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="mode === 'create' ? 'Tambah Barang' : 'Edit Barang'"></h3>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Barcode</label>
                                            <input type="text" x-model="form.barcode" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div x-show="errors.barcode" class="mt-2 text-sm text-red-600" x-text="errors.barcode"></div>
                                        </div>
 
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                                            <input type="text" x-model="form.nama" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div x-show="errors.nama" class="mt-2 text-sm text-red-600" x-text="errors.nama"></div>
                                        </div>
 
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                            <select x-model="form.kategori_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Pilih Kategori</option>
                                                @foreach($kategori as $k)
                                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                                @endforeach
                                            </select>
                                            <div x-show="errors.kategori_id" class="mt-2 text-sm text-red-600" x-text="errors.kategori_id"></div>
                                        </div>
 
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Satuan</label>
                                            <select x-model="form.satuan_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Pilih Satuan</option>
                                                @foreach($satuan as $s)
                                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                                @endforeach
                                            </select>
                                            <div x-show="errors.satuan_id" class="mt-2 text-sm text-red-600" x-text="errors.satuan_id"></div>
                                        </div>
 
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Stok Minimal</label>
                                            <input type="number" step="0.01" x-model="form.stok_minimal" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div x-show="errors.stok_minimal" class="mt-2 text-sm text-red-600" x-text="errors.stok_minimal"></div>
                                        </div>
 
                                        <div class="flex items-center">
                                            <input type="checkbox" x-model="form.bisa_ecer" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 block text-sm text-gray-900">Bisa Dijual Ecer</label>
                                        </div>
                                    </div>
 
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700" x-text="mode === 'create' ? 'Simpan' : 'Update'">
                                       </button>
                                   </div>
                               </form>
                           </div>
                       </div>
                   </div>

                   <!-- Delete Stock Modal -->
                   <div x-show="isDeleteStockModalOpen" 
                        class="fixed inset-0 z-50 overflow-y-auto"
                        style="display: none;">
                       <div class="flex items-center justify-center min-h-screen px-4">
                           <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeDeleteStockModal()"></div>

                           <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                               <div class="p-6">
                                   <div class="flex items-center mb-4">
                                       <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                                       <h3 class="text-lg font-medium text-gray-900">Hapus Record Stok</h3>
                                   </div>
                                   
                                   <div class="mb-4">
                                       <p class="text-sm text-gray-600 mb-3">Apakah Anda yakin ingin menghapus record stok ini?</p>
                                       
                                       <div class="bg-gray-50 p-3 rounded-lg" x-show="selectedStock">
                                           <div class="text-sm">
                                               <div><strong>Nota:</strong> <span x-text="selectedStock?.nomor_nota"></span></div>
                                               <div><strong>Tanggal:</strong> <span x-text="formatDate(selectedStock?.tanggal)"></span></div>
                                               <div><strong>Jumlah:</strong> <span x-text="selectedStock?.jumlah"></span></div>
                                           </div>
                                       </div>
                                       
                                       <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mt-3">
                                           <div class="text-sm text-yellow-800">
                                               <i class="fas fa-info-circle mr-1"></i>
                                               <strong>Perhatian:</strong> Stok barang akan dikurangi sesuai jumlah yang dihapus.
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="flex justify-end space-x-3">
                                       <button type="button" @click="closeDeleteStockModal()" 
                                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                           Batal
                                       </button>
                                       <button type="button" @click="deleteStock()" 
                                               class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                           Ya, Hapus
                                       </button>
                                   </div>
                               </div>
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
                                   <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Barang</h3>
                                   <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus barang ini?</p>
                                   
                                   <div class="mt-4 flex justify-end space-x-3">
                                       <button type="button" @click="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                           Batal
                                       </button>
                                       <button type="button" @click="destroy()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                           Hapus
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
       function barangHandler() {
           return {
               search: '',
               items: [],
               stocks: [],
               selectedItem: null,
               isModalOpen: false,
               isDeleteModalOpen: false,
               isStockModalOpen: false,
               isRestockModalOpen: false,
               isDeleteStockModalOpen: false,
               mode: 'create',
               selectedId: null,
               selectedStock: null,
               form: {
                   barcode: '',
                   nama: '',
                   kategori_id: '',
                   satuan_id: '',
                   stok_minimal: '',
                   bisa_ecer: false
               },
               restockForm: {
                   nomor_nota: '',
                   tanggal: new Date().toISOString().slice(0, 10),
                   barang_id: '',
                   stok: '',
                   harga_beli: '',
                   harga_jual: '',
                   harga_ecer: ''
               },
               errors: {},
               restockErrors: {},
               flash: {
                   message: ''
               },

               formatDate(date) {
                   return new Date(date).toLocaleDateString('id-ID', {
                       year: 'numeric',
                       month: 'long',
                       day: 'numeric'
                   });
               },

               formatCurrency(amount) {
                   return new Intl.NumberFormat('id-ID', {
                       style: 'currency',
                       currency: 'IDR'
                   }).format(amount);
               },

                               async generateNomorNota() {
                   try {
                       const response = await fetch('/barang-masuk/next-nomor-nota', {
                           headers: {
                               'Accept': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                           }
                       });
                       const data = await response.json();
                       return data.nomor_nota;
                   } catch (error) {
                       console.error('Error:', error);
                       return 'BM-1';
                   }
               },

               async openRestockModal(item) {
                   this.selectedItem = item;
                   this.restockForm.nomor_nota = await this.generateNomorNota();
                   this.restockForm.barang_id = item.id;
                   this.restockForm.tanggal = new Date().toISOString().slice(0, 10);
                   this.restockForm.stok = '';
                   this.restockForm.harga_beli = '';
                   this.restockForm.harga_jual = '';
                   this.restockForm.harga_ecer = '';
                   this.restockErrors = {};
                   this.isRestockModalOpen = true;
               },

               closeRestockModal() {
                   this.isRestockModalOpen = false;
                   this.selectedItem = null;
                   this.restockErrors = {};
               },

               async saveRestock() {
                   try {
                       // Copy form data dan pastikan jumlah = stok
                       const formData = { ...this.restockForm };
                       formData.jumlah = formData.stok;

                       const response = await fetch('/barang-masuk', {
                           method: 'POST',
                           headers: {
                               'Content-Type': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                               'Accept': 'application/json'
                           },
                           body: JSON.stringify(formData)
                       });

                       const result = await response.json();

                       if (!response.ok) {
                           if (response.status === 422) {
                               this.restockErrors = result.errors || {};
                               return;
                           }
                           throw new Error(result.error || result.message);
                       }

                       this.flash.message = `Stok berhasil ditambahkan! ${this.selectedItem.nama} +${this.restockForm.stok}`;
                       setTimeout(() => this.flash.message = '', 5000);
                       
                       this.closeRestockModal();
                       await this.loadData(); // Reload data barang
                   } catch (error) {
                       console.error('Error:', error);
                       alert(error.message || 'Terjadi kesalahan! Silakan coba lagi.');
                   }
               },

               async viewStock(item) {
                   try {
                       const response = await fetch(`/barang/${item.id}/stock`);
                       const data = await response.json();
                       
                       this.stocks = data;
                       this.selectedItem = item;
                       this.isStockModalOpen = true;
                   } catch (error) {
                       console.error('Error:', error);
                       alert('Gagal memuat data stok');
                   }
               },

               closeStockModal() {
                   this.isStockModalOpen = false;
                   this.selectedItem = null;
                   this.stocks = [];
               },

               confirmDeleteStock(stock) {
                   this.selectedStock = stock;
                   this.isDeleteStockModalOpen = true;
               },

               closeDeleteStockModal() {
                   this.isDeleteStockModalOpen = false;
                   this.selectedStock = null;
               },

               async deleteStock() {
                   try {
                       const response = await fetch(`/barang-masuk/${this.selectedStock.id}`, {
                           method: 'DELETE',
                           headers: {
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                               'Accept': 'application/json'
                           }
                       });

                       const result = await response.json();

                       if (!response.ok) {
                           throw new Error(result.error || result.message);
                       }

                       this.flash.message = `Record stok berhasil dihapus! Stok ${this.selectedItem.nama} dikurangi ${this.selectedStock.jumlah}`;
                       setTimeout(() => this.flash.message = '', 5000);
                       
                       this.closeDeleteStockModal();
                       
                       // Reload both stock history and main data
                       await this.viewStock(this.selectedItem);
                       await this.loadData();
                   } catch (error) {
                       console.error('Error:', error);
                       alert(error.message || 'Terjadi kesalahan! Silakan coba lagi.');
                   }
               },

               async loadData() {
                   try {
                       const response = await fetch(`/barang?search=${this.search}`, {
                           headers: {
                               'Accept': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                           }
                       });
                       if (!response.ok) throw new Error('Network response was not ok');
                       
                       this.items = await response.json();
                   } catch (error) {
                       console.error('Error:', error);
                       alert('Gagal memuat data');
                   }
               },

               resetForm() {
                   this.form = {
                       barcode: '',
                       nama: '',
                       kategori_id: '',
                       satuan_id: '',
                       stok_minimal: '',
                       bisa_ecer: false
                   };
                   this.errors = {};
               },

               closeModal() {
                   this.isModalOpen = false;
                   this.resetForm();
               },

               closeDeleteModal() {
                   this.isDeleteModalOpen = false;
                   this.selectedId = null;
               },

               edit(item) {
                   this.mode = 'edit';
                   this.selectedId = item.id;
                   this.form = {
                       barcode: item.barcode,
                       nama: item.nama,
                       kategori_id: item.kategori_id,
                       satuan_id: item.satuan_id,
                       stok_minimal: item.stok_minimal,
                       bisa_ecer: item.bisa_ecer
                   };
                   this.isModalOpen = true;
               },

               confirmDelete(id) {
                   this.selectedId = id;
                   this.isDeleteModalOpen = true;
               },

               async save() {
                   try {
                       const url = this.mode === 'create' ? '/barang' : `/barang/${this.selectedId}`;
                       const method = this.mode === 'create' ? 'POST' : 'PUT';
                       
                       const response = await fetch(url, {
                           method: method,
                           headers: {
                               'Content-Type': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                               'Accept': 'application/json'
                           },
                           body: JSON.stringify(this.form)
                       });

                       const result = await response.json();

                       if (!response.ok) {
                           if (response.status === 422) {
                               this.errors = result.errors;
                               return;
                           }
                           throw new Error(result.message);
                       }

                       this.flash.message = result.message;
                       setTimeout(() => this.flash.message = '', 3000);
                       
                       await this.loadData();
                       this.closeModal();
                   } catch (error) {
                       console.error('Error:', error);
                       alert('Terjadi kesalahan! Silakan coba lagi.');
                   }
               },

               async destroy() {
                   try {
                       const response = await fetch(`/barang/${this.selectedId}`, {
                           method: 'DELETE',
                           headers: {
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                               'Accept': 'application/json'
                           }
                       });

                       const result = await response.json();

                       if (!response.ok) {
                           throw new Error(result.message);
                       }

                       this.flash.message = result.message;
                       setTimeout(() => this.flash.message = '', 3000);
                       
                       await this.loadData();
                       this.closeDeleteModal();
                   } catch (error) {
                       console.error('Error:', error);
                       alert(error.message || 'Terjadi kesalahan! Silakan coba lagi.');
                   }
               }
           }
       }
   </script>
   @endpush
</x-app-layout>