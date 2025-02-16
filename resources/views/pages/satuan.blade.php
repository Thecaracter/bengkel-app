<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Data Satuan</h2>
    </x-slot>
 
    <div class="py-12" x-data="satuanHandler()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="mb-6 flex justify-end">
                        <button @click="isModalOpen = true; mode = 'create'; resetForm()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah Satuan
                        </button>
                    </div>
 
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konversi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($satuans as $satuan)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $satuan->nama }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $satuan->konversi }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <button @click="edit({{ $satuan }})" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                <button @click="confirmDelete({{ $satuan->id }})" class="text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
 
                    <!-- Modal Form -->
                    <div x-show="isModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>
 
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form @submit.prevent="save" class="p-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" 
                                    x-text="mode === 'create' ? 'Tambah Satuan' : 'Edit Satuan'">
                                </h3>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Satuan</label>
                                            <input type="text" x-model="form.nama" id="nama" 
                                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="Masukkan nama satuan">
                                            <div x-show="errors.nama" class="mt-2 text-sm text-red-600" x-text="errors.nama"></div>
                                        </div>
                                        
                                        <div>
                                            <label for="konversi" class="block text-sm font-medium text-gray-700">Nilai Konversi</label>
                                            <input type="number" step="0.01" x-model="form.konversi" id="konversi"
                                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="Masukkan nilai konversi">
                                            <div x-show="errors.konversi" class="mt-2 text-sm text-red-600" x-text="errors.konversi"></div>
                                        </div>
                                    </div>
 
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button" @click="closeModal()"
                                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Batal
                                        </button>
                                    <button type="submit"
        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        x-text="mode === 'create' ? 'Simpan' : 'Update'">
</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
 
                    <!-- Delete Modal -->
                    <div x-show="isDeleteModalOpen" 
                         class="fixed inset-0 z-50 overflow-y-auto"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeDeleteModal()"></div>
 
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg font-medium text-gray-900">Hapus Satuan</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Apakah Anda yakin ingin menghapus satuan ini? Tindakan ini tidak dapat dibatalkan.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" @click="destroy()"
                                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        Hapus
                                    </button>
                                    <button type="button" @click="closeDeleteModal()"
                                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Alert -->
                    <div x-show="flash.message" 
                         x-transition:enter="transform ease-out duration-300"
                         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed bottom-0 right-0 flex items-center justify-between max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 border-green-500 px-4 py-3 m-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p x-text="flash.message" class="ml-3 text-sm font-medium text-gray-900"></p>
                        </div>
                        <button @click="flash.message = ''" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    @push('scripts')
    <script>
        function satuanHandler() {
            return {
                isModalOpen: false,
                isDeleteModalOpen: false,
                mode: 'create',
                selectedId: null,
                form: {
                    nama: '',
                    konversi: ''
                },
                errors: {},
                flash: {
                    message: ''
                },
 
                resetForm() {
                   this.form = {
                       nama: '',
                       konversi: ''
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

               edit(satuan) {
                   this.mode = 'edit';
                   this.selectedId = satuan.id;
                   this.form = {
                       nama: satuan.nama,
                       konversi: satuan.konversi
                   };
                   this.isModalOpen = true;
               },

               confirmDelete(id) {
                   this.selectedId = id;
                   this.isDeleteModalOpen = true;
               },

               async save() {
                   try {
                       const url = this.mode === 'create' ? '/satuan' : `/satuan/${this.selectedId}`;
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
                       
                       this.closeModal();
                       window.location.reload();
                   } catch (error) {
                       console.error('Error:', error);
                       alert('Terjadi kesalahan! Silakan coba lagi.');
                   }
               },

               async destroy() {
                   try {
                       const response = await fetch(`/satuan/${this.selectedId}`, {
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
                       
                       this.closeDeleteModal();
                       window.location.reload();
                   } catch (error) {
                       console.error('Error:', error);
                       alert('Terjadi kesalahan! Silakan coba lagi.');
                   }
               }
           }
       }
   </script>
   @endpush
</x-app-layout>