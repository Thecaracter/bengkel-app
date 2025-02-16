<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Dashboard') }}</h2>
    </x-slot>
 
    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Total Barang -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="text-2xl text-blue-600 fas fa-box"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Barang</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalBarang }}</p>
                </div>
            </div>
        </div>
 
        <!-- Stok Minimal -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="text-2xl text-yellow-600 fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Stok Minimal</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stokMinimal }}</p>
                </div>
            </div>
        </div>
 
        <!-- Barang Masuk -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Barang Masuk</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $barangMasukHariIni }}</p>
                    <p class="text-sm text-gray-500">Hari Ini</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900">{{ $barangMasukBulanIni }}</p>
                    <p class="text-sm text-gray-500">Bulan Ini</p>
                </div>
            </div>
        </div>
 
        <!-- Barang Keluar -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Barang Keluar</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $barangKeluarHariIni }}</p>
                    <p class="text-sm text-gray-500">Hari Ini</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900">{{ $barangKeluarBulanIni }}</p>
                    <p class="text-sm text-gray-500">Bulan Ini</p>
                </div>
            </div>
        </div>
 
        <!-- Penjualan -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Penjualan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Hari Ini</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($penjualanBulanIni, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Bulan Ini</p>
                </div>
            </div>
        </div>
 
        <div class="col-span-full">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Chart Barang -->
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Statistik Barang</h3>
                    <div class="h-80">
                        <canvas id="barangChart"></canvas>
                    </div>
                </div>
 
                <!-- Chart Penjualan -->  
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Statistik Penjualan</h3>
                    <div class="h-80">
                        <canvas id="penjualanChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart Barang
        new Chart(document.getElementById('barangChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($dailyMasuk->toArray())) !!},
                datasets: [{
                    label: 'Barang Masuk',
                    data: {!! json_encode(array_values($dailyMasuk->toArray())) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    tension: 0.1
                }, {
                    label: 'Barang Keluar',
                    data: {!! json_encode(array_values($dailyKeluar->toArray())) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
 
        // Chart Penjualan
        new Chart(document.getElementById('penjualanChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($dailyPenjualan->toArray())) !!},
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: {!! json_encode(array_values($dailyPenjualan->toArray())) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
 </x-app-layout>