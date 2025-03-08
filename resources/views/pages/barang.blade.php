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
 
                    <!-- Table -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Minimal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ecer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in items" :key="item.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.barcode || '-'"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.nama"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.kategori.nama"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.satuan.nama"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.stok"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.stok_minimal"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <span :class="item.bisa_ecer ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                  x-text="item.bisa_ecer ? 'Ya' : 'Tidak'"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-3">
                                                <button @click="viewStock(item)" class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-box"></i>
                                                </button>
                                                <button @click="edit(item)" class="text-indigo-600 hover:text-indigo-900">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="confirmDelete(item.id)" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">Tidak ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
 
                    <!-- Stock Modal -->
                    <div x-show="isStockModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeStockModal()"></div>
 
                            <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Riwayat Stok <span x-text="selectedItem?.nama" class="font-bold"></span></h3>
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
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Stok</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Ecer</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="stock in stocks" :key="stock.nomor_nota">
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(stock.tanggal)"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.nomor_nota"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.stok"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.totalStok"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(stock.harga_beli)"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(stock.harga_jual)"></td>
                                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="stock.harga_ecer ? formatCurrency(stock.harga_ecer) : '-'"></td>
                                                    </tr>
                                                </template>
                                                <tr x-show="stocks.length === 0">
                                                    <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">Tidak ada data stok</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Modal Form -->
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
                                            <label class="ml-2 block text-sm text-gray-900">Bisa Ecer</label>
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
               mode: 'create',
               selectedId: null,
               form: {
                   barcode: '',
                   nama: '',
                   kategori_id: '',
                   satuan_id: '',
                   stok_minimal: '',
                   bisa_ecer: false
               },
               errors: {},
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

               async loadData() {
    try {
        const response = await fetch(`/barang?search=${this.search}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        if (!response.ok) throw new Error('Network response was not ok');
        
        // Ambil data barang
        const items = await response.json();
        
        // Proses untuk menghitung total stok
        this.items = await Promise.all(items.map(async (item) => {
            const stockResponse = await fetch(`/barang/${item.id}/stock`);
            const stockData = await stockResponse.json();
            
            // Hitung total stok
            item.stok = stockData.length > 0 ? stockData[stockData.length - 1].totalStok : 0;
            
            return item;
        }));
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