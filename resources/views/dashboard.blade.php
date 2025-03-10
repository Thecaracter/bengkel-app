<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Dashboard') }}</h2>
            
            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-4 mt-2 md:mt-0">
                <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <select name="filter_type" id="filter_type" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Bulan Ini</option>
                        <option value="month" {{ request('filter_type') == 'month' ? 'selected' : '' }}>Berdasarkan Bulan</option>
                        <option value="year" {{ request('filter_type') == 'year' ? 'selected' : '' }}>Berdasarkan Tahun</option>
                        <option value="custom" {{ request('filter_type') == 'custom' ? 'selected' : '' }}>Periode Kustom</option>
                    </select>
                    
                    @if(request('filter_type') == 'month')
                        <input type="month" name="filter_month" value="{{ request('filter_month', date('Y-m')) }}" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @endif
                    
                    @if(request('filter_type') == 'year')
                        <select name="filter_year" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                <option value="{{ $year }}" {{ request('filter_year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    @endif
                    
                    @if(request('filter_type') == 'custom')
                        <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-01')) }}" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-t')) }}" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="submit" class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Filter</button>
                    @endif
                </form>
            </div>
        </div>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $barangMasukPeriode }}</p>
                    <p class="text-sm text-gray-500">Periode</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $barangKeluarPeriode }}</p>
                    <p class="text-sm text-gray-500">Periode</p>
                </div>
            </div>
        </div>
 
        <!-- Penghasilan Kotor (Penjualan) -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Penghasilan Kotor</p>
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Hari Ini</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($penjualanPeriode, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Periode</p>
                </div>
            </div>
        </div>

        <!-- Total Pengeluaran -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-red-600">Rp {{ number_format($pengeluaranHariIni, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Hari Ini</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-red-600">Rp {{ number_format($pengeluaranPeriode, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Periode</p>
                </div>
            </div>
        </div>

        <!-- Penghasilan Bersih (Keuntungan) -->
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Penghasilan Bersih</p>
                    <p class="text-2xl font-bold {{ $keuntunganHariIni >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($keuntunganHariIni, 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-500">Hari Ini</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold {{ $keuntunganPeriode >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($keuntunganPeriode, 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-500">Periode</p>
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
 
                <!-- Chart Keuangan -->  
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Statistik Keuangan</h3>
                    <div class="h-80">
                        <canvas id="keuanganChart"></canvas>
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
 
        // Chart Keuangan
        new Chart(document.getElementById('keuanganChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($period) !!},
                datasets: [{
                    label: 'Penjualan',
                    data: {!! json_encode($salesData) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }, {
                    label: 'Pengeluaran',
                    data: {!! json_encode($expenseData) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }, {
                    label: 'Keuntungan',
                    data: {!! json_encode($profitData) !!},
                    type: 'line',
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>