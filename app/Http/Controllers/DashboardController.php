<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalBarang = Barang::count();
        $stokMinimal = Barang::where('stok', '<=', 'stok_minimal')->count();

        $now = Carbon::now();
        $barangMasukHariIni = BarangMasuk::whereDate('tanggal', $now)->sum('stok');
        $barangKeluarHariIni = BarangKeluar::whereDate('tanggal', $now)->sum('jumlah');

        $barangMasukBulanIni = BarangMasuk::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('stok');

        $barangKeluarBulanIni = BarangKeluar::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('jumlah');

        $penjualanHariIni = BarangKeluar::whereDate('tanggal', $now)
            ->sum(DB::raw('jumlah * harga'));

        $penjualanBulanIni = BarangKeluar::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum(DB::raw('jumlah * harga'));

        $last30Days = collect(range(29, 0))->map(fn($day) => Carbon::now()->subDays($day));

        $dailyMasuk = $last30Days->mapWithKeys(function ($date) {
            return [$date->format('Y-m-d') => BarangMasuk::whereDate('tanggal', $date)->sum('stok')];
        });

        $dailyKeluar = $last30Days->mapWithKeys(function ($date) {
            return [$date->format('Y-m-d') => BarangKeluar::whereDate('tanggal', $date)->sum('jumlah')];
        });

        $dailyPenjualan = $last30Days->mapWithKeys(function ($date) {
            return [
                $date->format('Y-m-d') => BarangKeluar::whereDate('tanggal', $date)
                    ->sum(DB::raw('jumlah * harga'))
            ];
        });

        return view('dashboard', compact(
            'totalBarang',
            'stokMinimal',
            'barangMasukHariIni',
            'barangKeluarHariIni',
            'barangMasukBulanIni',
            'barangKeluarBulanIni',
            'penjualanHariIni',
            'penjualanBulanIni',
            'dailyMasuk',
            'dailyKeluar',
            'dailyPenjualan'
        ));
    }
}