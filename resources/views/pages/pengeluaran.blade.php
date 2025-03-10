<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Data Pengeluaran</h2>
    </x-slot>
 
    <div class="py-12" x-data="pengeluaranHandler()" x-init="loadData()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <!-- Search & Filter & Add Button -->
                    <div class="mb-6 space-y-4">
                        <div class="flex flex-wrap gap-4 items-center justify-between">
                            <div class="w-64 relative">
                                <input type="text" 
                                       x-model="search" 
                                       @input.debounce.300ms="loadData()"
                                       placeholder="Cari pengeluaran..." 
                                       class="w-full px-4 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                            </div>
     
                            <button @click="isModalOpen = true; mode = 'create'; resetForm()" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Pengeluaran
                            </button>
                        </div>

                        <!-- Filter Controls -->
                        <div class="flex flex-wrap gap-4 border-t border-gray-200 pt-4">
                            <div class="flex items-center">
                                <label class="mr-2 text-sm font-medium text-gray-700">Filter:</label>
                                <select x-model="filterType" @change="resetFilterValues(); loadData()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Semua Data</option>
                                    <option value="month">Berdasarkan Bulan</option>
                                    <option value="year">Berdasarkan Tahun</option>
                                    <option value="custom">Periode Kustom</option>
                                </select>
                            </div>

                            <!-- Month Filter -->
                            <div x-show="filterType === 'month'" class="flex items-center">
                                <label class="mr-2 text-sm font-medium text-gray-700">Bulan:</label>
                                <input type="month" x-model="filterMonth" @change="loadData()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <!-- Year Filter -->
                            <div x-show="filterType === 'year'" class="flex items-center">
                                <label class="mr-2 text-sm font-medium text-gray-700">Tahun:</label>
                                <select x-model="filterYear" @change="loadData()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Pilih Tahun</option>
                                    <template x-for="year in getYearOptions()" :key="year">
                                        <option :value="year" x-text="year"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Custom Date Range Filter -->
                            <div x-show="filterType === 'custom'" class="flex flex-wrap items-center gap-2">
                                <label class="text-sm font-medium text-gray-700">Dari:</label>
                                <input type="date" x-model="startDate" @change="loadData()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                
                                <label class="text-sm font-medium text-gray-700">Sampai:</label>
                                <input type="date" x-model="endDate" @change="loadData()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Total Summary -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200" x-show="total > 0">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-blue-800">Total Pengeluaran</h3>
                                <p class="text-sm text-blue-600" x-show="filterType === 'month' && filterMonth">
                                    Bulan: <span x-text="formatMonthYear(filterMonth)"></span>
                                </p>
                                <p class="text-sm text-blue-600" x-show="filterType === 'year' && filterYear">
                                    Tahun: <span x-text="filterYear"></span>
                                </p>
                                <p class="text-sm text-blue-600" x-show="filterType === 'custom' && startDate && endDate">
                                    Periode: <span x-text="formatDate(startDate) + ' - ' + formatDate(endDate)"></span>
                                </p>
                            </div>
                            <div class="text-2xl font-bold text-blue-800" x-text="formatCurrency(total)"></div>
                        </div>
                    </div>
 
                    <!-- Table -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Pengeluaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in items" :key="item.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatDate(item.tanggal)"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="item.nama_pengeluaran"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900" x-text="formatCurrency(item.jumlah)"></td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-3">
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
                                    <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">Tidak ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
 
                    <!-- Modal Form -->
                    <div x-show="isModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto" 
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>
 
                            <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <form @submit.prevent="save" class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="mode === 'create' ? 'Tambah Pengeluaran' : 'Edit Pengeluaran'"></h3>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nama Pengeluaran</label>
                                            <input type="text" x-model="form.nama_pengeluaran" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div x-show="errors.nama_pengeluaran" class="mt-2 text-sm text-red-600" x-text="errors.nama_pengeluaran"></div>
                                        </div>
 
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Jumlah</label>
                                            <input type="number" step="0.01" x-model="form.jumlah" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div x-show="errors.jumlah" class="mt-2 text-sm text-red-600" x-text="errors.jumlah"></div>
                                        </div>
 
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                            <input type="date" x-model="form.tanggal" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div x-show="errors.tanggal" class="mt-2 text-sm text-red-600" x-text="errors.tanggal"></div>
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
                                   <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Pengeluaran</h3>
                                   <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus data pengeluaran ini?</p>
                                   
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
       function pengeluaranHandler() {
           return {
               search: '',
               items: [],
               total: 0,
               filterType: '',
               filterMonth: new Date().toISOString().slice(0, 7), // Format: YYYY-MM
               filterYear: new Date().getFullYear().toString(),
               startDate: new Date().toISOString().slice(0, 10),
               endDate: new Date().toISOString().slice(0, 10),
               isModalOpen: false,
               isDeleteModalOpen: false,
               mode: 'create',
               selectedId: null,
               form: {
                   nama_pengeluaran: '',
                   jumlah: '',
                   tanggal: new Date().toISOString().slice(0, 10)
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

               formatMonthYear(monthStr) {
                   const date = new Date(monthStr + "-01");
                   return date.toLocaleDateString('id-ID', {
                       year: 'numeric',
                       month: 'long'
                   });
               },

               formatCurrency(amount) {
                   return new Intl.NumberFormat('id-ID', {
                       style: 'currency',
                       currency: 'IDR'
                   }).format(amount);
               },

               getYearOptions() {
                   const currentYear = new Date().getFullYear();
                   const years = [];
                   for (let i = currentYear - 5; i <= currentYear; i++) {
                       years.push(i.toString());
                   }
                   return years;
               },

               resetFilterValues() {
                   if (this.filterType !== 'month') this.filterMonth = new Date().toISOString().slice(0, 7);
                   if (this.filterType !== 'year') this.filterYear = new Date().getFullYear().toString();
                   if (this.filterType !== 'custom') {
                       this.startDate = new Date().toISOString().slice(0, 10);
                       this.endDate = new Date().toISOString().slice(0, 10);
                   }
               },

               async loadData() {
                   try {
                       let url = `/pengeluaran?search=${this.search}`;
                       
                       // Tambahkan parameter filter berdasarkan tipe filter yang dipilih
                       if (this.filterType === 'month' && this.filterMonth) {
                           url += `&filter_type=month&filter_value=${this.filterMonth}`;
                       } else if (this.filterType === 'year' && this.filterYear) {
                           url += `&filter_type=year&filter_value=${this.filterYear}`;
                       } else if (this.filterType === 'custom' && this.startDate && this.endDate) {
                           url += `&start_date=${this.startDate}&end_date=${this.endDate}`;
                       }
                       
                       const response = await fetch(url, {
                           headers: {
                               'Accept': 'application/json',
                               'X-Requested-With': 'XMLHttpRequest',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                           }
                       });
                       
                       if (!response.ok) throw new Error('Network response was not ok');
                       
                       const result = await response.json();
                       this.items = result.data;
                       this.total = result.total || 0;
                   } catch (error) {
                       console.error('Error:', error);
                       alert('Gagal memuat data: ' + error.message);
                   }
               },

               resetForm() {
                   this.form = {
                       nama_pengeluaran: '',
                       jumlah: '',
                       tanggal: new Date().toISOString().slice(0, 10)
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
                       nama_pengeluaran: item.nama_pengeluaran,
                       jumlah: item.jumlah,
                       tanggal: new Date(item.tanggal).toISOString().slice(0, 10)
                   };
                   this.isModalOpen = true;
               },

               confirmDelete(id) {
                   this.selectedId = id;
                   this.isDeleteModalOpen = true;
               },

               async save() {
                   try {
                       const url = this.mode === 'create' ? '/pengeluaran' : `/pengeluaran/${this.selectedId}`;
                       const method = this.mode === 'create' ? 'POST' : 'PUT';
                       
                       const response = await fetch(url, {
                           method: method,
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
                       const response = await fetch(`/pengeluaran/${this.selectedId}`, {
                           method: 'DELETE',
                           headers: {
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                               'Accept': 'application/json',
                               'X-Requested-With': 'XMLHttpRequest'
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