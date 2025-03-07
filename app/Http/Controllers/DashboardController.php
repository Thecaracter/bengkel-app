<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalBarang = Barang::count();
        // Perbaikan query stok minimal - gunakan operator DB::raw untuk perbandingan kolom
        $stokMinimal = Barang::whereRaw('stok <= stok_minimal')->count();

        $now = Carbon::now();

        // Barang masuk hari ini dan bulan ini - gunakan jumlah bukan stok
        $barangMasukHariIni = BarangMasuk::whereDate('tanggal', $now)->sum('jumlah');
        $barangMasukBulanIni = BarangMasuk::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('jumlah');

        // Barang keluar hari ini dan bulan ini - data dari tabel barang_keluar_detail
        $barangKeluarHariIni = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($now) {
            $query->whereDate('tanggal', $now);
        })->sum('jumlah');

        $barangKeluarBulanIni = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($now) {
            $query->whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year);
        })->sum('jumlah');

        // Penjualan hari ini
        $penjualanHariIni = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($now) {
            $query->whereDate('tanggal', $now);
        })->sum(DB::raw('jumlah * harga'));

        // Penjualan bulan ini
        $penjualanBulanIni = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($now) {
            $query->whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year);
        })->sum(DB::raw('jumlah * harga'));

        $last30Days = collect(range(29, 0))->map(fn($day) => Carbon::now()->subDays($day));

        // Statistik daily untuk barang masuk
        $dailyMasuk = $last30Days->mapWithKeys(function ($date) {
            return [$date->format('Y-m-d') => BarangMasuk::whereDate('tanggal', $date)->sum('jumlah')];
        });

        // Statistik daily untuk barang keluar
        $dailyKeluar = $last30Days->mapWithKeys(function ($date) {
            return [
                $date->format('Y-m-d') => BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal', $date);
                })->sum('jumlah')
            ];
        });

        // Statistik daily untuk penjualan
        $dailyPenjualan = $last30Days->mapWithKeys(function ($date) {
            return [
                $date->format('Y-m-d') => BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal', $date);
                })->sum(DB::raw('jumlah * harga'))
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