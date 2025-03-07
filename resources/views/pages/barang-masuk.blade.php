<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Barang Masuk</h2>
            <button onclick="showCreateModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Barang Masuk
            </button>
        </div>
    </x-slot>
 
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <input type="date" id="start_date" class="rounded-md border-gray-300">
                <input type="date" id="end_date" class="rounded-md border-gray-300">
                <input type="text" id="search" placeholder="Cari nota/barang..." class="rounded-md border-gray-300 flex-1">
                <button onclick="searchData()" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search"></i>
                </button>
            </div>
 
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Nota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Awal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Tersisa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Ecer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($barangMasuk as $bm)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $bm->tanggal->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $bm->nomor_nota }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $bm->barang->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($bm->jumlah, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($bm->stok, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($bm->harga_beli, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($bm->harga_jual, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($bm->harga_ecer)
                                        Rp {{ number_format($bm->harga_ecer, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="deleteData({{ $bm->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $barangMasuk->links() }}
            </div>
        </div>
    </div>
 
    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Tambah Barang Masuk</h3>
                    <form id="createForm" onsubmit="saveBarangMasuk(event)">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Nota</label>
                                <input type="text" name="nomor_nota" id="nomorNota" value="{{ $nextNomorNota ?? 'BM-1' }}" class="mt-1 block w-full rounded-md border-gray-300" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Barcode</label>
                                <input type="text" id="barcodeInput" onkeyup="scanBarcode(this)" 
                                       class="mt-1 block w-full rounded-md border-gray-300" placeholder="Scan barcode...">
                            </div>
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700">Barang</label>
                                <div class="relative">
                                    <input type="text" id="barangSearch" onkeyup="searchBarang(this)" 
                                           placeholder="Cari nama barang..." 
                                           class="mt-1 block w-full rounded-md border-gray-300" 
                                           autocomplete="off">
                                    <input type="hidden" name="barang_id" id="barangId" required>
                                    <div id="barangDropdown" style="display:none" 
                                         class="absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto mt-1">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah</label>
                                <input type="number" name="stok" step="0.01" class="mt-1 block w-full rounded-md border-gray-300" required>
                                <p class="mt-1 text-sm text-gray-500">Jumlah ini juga akan menjadi stok awal</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harga Beli</label>
                                <input type="number" name="harga_beli" class="mt-1 block w-full rounded-md border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harga Jual</label>
                                <input type="number" name="harga_jual" class="mt-1 block w-full rounded-md border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harga Ecer</label>
                                <input type="number" name="harga_ecer" class="mt-1 block w-full rounded-md border-gray-300">
                                <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak dijual ecer</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="hideCreateModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg">Batal</button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 
    @push('scripts')
    <script>
        let dropdownResults = [];
        let selectedIndex = -1;
 
        function showDropdown() {
            const dropdown = document.getElementById('barangDropdown');
            dropdown.style.display = 'block';
        }
 
        function hideDropdown() {
            const dropdown = document.getElementById('barangDropdown');
            dropdown.style.display = 'none';
            selectedIndex = -1;
        }
 
        function selectBarang(item) {
            const barang = JSON.parse(decodeURIComponent(item));
            document.getElementById('barangSearch').value = barang.nama;
            document.getElementById('barangId').value = barang.id;
            document.getElementById('barcodeInput').value = barang.barcode || '';
            hideDropdown();
        }
 
        function updateDropdown() {
            const dropdown = document.getElementById('barangDropdown');
            
            dropdown.innerHTML = dropdownResults.map((item, index) => `
                <div onclick='selectBarang("${encodeURIComponent(JSON.stringify(item))}")' 
                     class="px-4 py-2 cursor-pointer ${index === selectedIndex ? 'bg-blue-100' : 'hover:bg-gray-100'}">
                    ${item.barcode ? `<span class="text-gray-500">${item.barcode}</span> - ` : ''}${item.nama}
                </div>
            `).join('');
 
            if (selectedIndex >= 0) {
                const selected = dropdown.children[selectedIndex];
                selected.scrollIntoView({ block: 'nearest' });
            }
        }
 
        function handleKeydown(e) {
            if (dropdownResults.length === 0) return;
 
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, dropdownResults.length - 1);
                    updateDropdown();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateDropdown();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0) {
                        selectBarang(encodeURIComponent(JSON.stringify(dropdownResults[selectedIndex])));
                    }
                    break;
                case 'Escape':
                    hideDropdown();
                    break;
            }
        }
 
        let searchTimeout;
        async function searchBarang(input) {
            clearTimeout(searchTimeout);
            
            if (input.value.length < 2) {
                hideDropdown();
                return;
            }
 
            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/barang-masuk/search-barang?search=${input.value}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    dropdownResults = await response.json();
                    if (dropdownResults.length > 0) {
                        showDropdown();
                        selectedIndex = -1;
                        updateDropdown();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }, 300);
        }
 
        let barcodeTimeout;
        async function scanBarcode(input) {
            clearTimeout(barcodeTimeout);
            
            if (!input.value) return;
            
            barcodeTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/barang-masuk/search-barang?barcode=${input.value}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    const data = await response.json();
                    if (data.length > 0) {
                        selectBarang(encodeURIComponent(JSON.stringify(data[0])));
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }, 500);
        }
 
        function showCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            document.getElementById('barcodeInput').focus();
            
            // Muat nomor nota terbaru setiap kali modal ditampilkan
            refreshNomorNota();
        }
 
        async function refreshNomorNota() {
            try {
                const response = await fetch('/barang-masuk/next-nomor-nota', {
                    headers: { 'Accept': 'application/json' }
                });
                
                const data = await response.json();
                document.getElementById('nomorNota').value = data.nomor_nota;
            } catch (error) {
                console.error('Error fetching nomor nota:', error);
            }
        }
 
        function hideCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
            document.getElementById('createForm').reset();
            hideDropdown();
        }
 
        async function saveBarangMasuk(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
 
            try {
                const response = await fetch("{{ route('barang-masuk.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
 
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Terjadi kesalahan');
 
                hideCreateModal();
                window.location.href = data.redirect;
            } catch (error) {
                alert(error.message);
            }
        }
 
        async function deleteData(id) {
            if(!confirm('Yakin ingin menghapus data ini?')) return;
            
            try {
                const response = await fetch(`/barang-masuk/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
 
                const data = await response.json();
                if (!response.ok) throw new Error(data.error);
                
                window.location.reload();
            } catch (error) {
                alert(error.message);
            }
        }
 
        async function searchData() {
            const params = new URLSearchParams({
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                search: document.getElementById('search').value
            });
 
            try {
                window.location.href = `{{ route('barang-masuk.index') }}?${params}`;
            } catch (error) {
                alert('Error searching data');
            }
        }

        document.getElementById('barangSearch').addEventListener('keydown', handleKeydown);
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#barangDropdown') && !e.target.closest('#barangSearch')) {
                hideDropdown();
            }
        });

        // Initialize search params from URL if exists
        window.addEventListener('load', function() {
            const params = new URLSearchParams(window.location.search);
            document.getElementById('start_date').value = params.get('start_date') || '';
            document.getElementById('end_date').value = params.get('end_date') || '';
            document.getElementById('search').value = params.get('search') || '';
        });
    </script>
    @endpush
</x-app-layout>