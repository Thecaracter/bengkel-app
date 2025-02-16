<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Data Kategori</h2>
    </x-slot>

    <div class="py-12" x-data="kategoriHandler()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="mb-6 flex justify-end">
                        <button @click="isModalOpen = true; mode = 'create'; resetForm()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah Kategori
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($kategori as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->nama }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-3">
                                                <button @click="edit({{ $item }})" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                <button @click="confirmDelete({{ $item->id }})" class="text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-sm text-gray-500 text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modal Form -->
                    <div x-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>

                            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <form @submit.prevent="save" class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="mode === 'create' ? 'Tambah Kategori' : 'Edit Kategori'"></h3>
                                    
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                                        <input type="text" x-model="form.nama" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <div x-show="errors.nama" class="mt-2 text-sm text-red-600" x-text="errors.nama"></div>
                                    </div>

                                    <div class="flex justify-end space-x-3">
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
                    <div x-show="isDeleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeDeleteModal()"></div>

                            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Kategori</h3>
                                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus kategori ini?</p>
                                    
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
                    <div x-show="flash.message" class="fixed bottom-0 right-0 m-4 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 border-green-500 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <p x-text="flash.message" class="text-sm font-medium text-gray-900"></p>
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
    </div>

    @push('scripts')
    <script>
        function kategoriHandler() {
            return {
                isModalOpen: false,
                isDeleteModalOpen: false,
                mode: 'create',
                selectedId: null,
                form: {
                    nama: ''
                },
                errors: {},
                flash: {
                    message: ''
                },

                resetForm() {
                    this.form = {
                        nama: ''
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

                edit(kategori) {
                    this.mode = 'edit';
                    this.selectedId = kategori.id;
                    this.form = {
                        nama: kategori.nama
                    };
                    this.isModalOpen = true;
                },

                confirmDelete(id) {
                    this.selectedId = id;
                    this.isDeleteModalOpen = true;
                },

                async save() {
                    try {
                        const url = this.mode === 'create' ? '/kategori' : `/kategori/${this.selectedId}`;
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
                        const response = await fetch(`/kategori/${this.selectedId}`, {
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