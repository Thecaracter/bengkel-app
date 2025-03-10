<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filter_type = $request->get('filter_type', '');
        $filter_month = $request->get('filter_month', Carbon::now()->format('Y-m'));
        $filter_year = $request->get('filter_year', Carbon::now()->year);
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $now = Carbon::now();

        // Set filter date range berdasarkan tipe filter
        if ($filter_type === 'month') {
            list($year, $month) = explode('-', $filter_month);
            $start_date = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $end_date = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } elseif ($filter_type === 'year') {
            $start_date = Carbon::createFromDate($filter_year, 1, 1)->startOfYear()->format('Y-m-d');
            $end_date = Carbon::createFromDate($filter_year, 12, 31)->endOfYear()->format('Y-m-d');
        }

        // Data default
        $totalBarang = Barang::count();
        $stokMinimal = Barang::whereRaw('stok <= stok_minimal')->count();

        // Barang masuk hari ini dan dalam periode
        $barangMasukHariIni = BarangMasuk::whereDate('tanggal', $now)->sum('jumlah');
        $barangMasukPeriode = BarangMasuk::whereBetween('tanggal', [$start_date, $end_date])->sum('jumlah');

        // Barang keluar hari ini dan dalam periode
        $barangKeluarHariIni = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($now) {
            $query->whereDate('tanggal', $now);
        })->sum('jumlah');

        $barangKeluarPeriode = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($start_date, $end_date) {
            $query->whereBetween('tanggal', [$start_date, $end_date]);
        })->sum('jumlah');

        // Penjualan (Penghasilan Kotor) hari ini
        $penjualanHariIni = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($now) {
            $query->whereDate('tanggal', $now);
        })->sum(DB::raw('jumlah * harga'));

        // Penjualan (Penghasilan Kotor) dalam periode
        $penjualanPeriode = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($start_date, $end_date) {
            $query->whereBetween('tanggal', [$start_date, $end_date]);
        })->sum(DB::raw('jumlah * harga'));

        // Modal (Harga Beli) untuk barang yang terjual hari ini
        $modalHariIni = DB::table('barang_keluar_detail as bkd')
            ->join('barang_masuk as bm', 'bkd.barang_masuk_id', '=', 'bm.id')
            ->join('barang_keluar as bk', 'bkd.barang_keluar_id', '=', 'bk.id')
            ->whereDate('bk.tanggal', $now)
            ->sum(DB::raw('bkd.jumlah * bm.harga_beli'));

        // Modal (Harga Beli) untuk barang yang terjual dalam periode
        $modalPeriode = DB::table('barang_keluar_detail as bkd')
            ->join('barang_masuk as bm', 'bkd.barang_masuk_id', '=', 'bm.id')
            ->join('barang_keluar as bk', 'bkd.barang_keluar_id', '=', 'bk.id')
            ->whereBetween('bk.tanggal', [$start_date, $end_date])
            ->sum(DB::raw('bkd.jumlah * bm.harga_beli'));

        // Total pengeluaran hari ini
        $pengeluaranHariIni = Pengeluaran::whereDate('tanggal', $now)->sum('jumlah');

        // Total pengeluaran dalam periode
        $pengeluaranPeriode = Pengeluaran::whereBetween('tanggal', [$start_date, $end_date])->sum('jumlah');

        // Penghasilan bersih (Penjualan - Modal - Pengeluaran)
        $keuntunganHariIni = $penjualanHariIni - $modalHariIni - $pengeluaranHariIni;
        $keuntunganPeriode = $penjualanPeriode - $modalPeriode - $pengeluaranPeriode;

        // Data untuk chart
        $period = [];
        $salesData = [];
        $expenseData = [];
        $profitData = [];

        if ($filter_type === 'month') {
            // Data per hari dalam bulan
            $startDate = Carbon::parse($start_date);
            $endDate = Carbon::parse($end_date);

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $currentDate = $date->format('Y-m-d');
                $period[] = $date->format('d');

                // Penjualan harian
                $dailySales = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($currentDate) {
                    $query->whereDate('tanggal', $currentDate);
                })->sum(DB::raw('jumlah * harga'));
                $salesData[] = $dailySales;

                // Pengeluaran harian
                $dailyExpense = Pengeluaran::whereDate('tanggal', $currentDate)->sum('jumlah');
                $expenseData[] = $dailyExpense;

                // Modal harian
                $dailyCost = DB::table('barang_keluar_detail as bkd')
                    ->join('barang_masuk as bm', 'bkd.barang_masuk_id', '=', 'bm.id')
                    ->join('barang_keluar as bk', 'bkd.barang_keluar_id', '=', 'bk.id')
                    ->whereDate('bk.tanggal', $currentDate)
                    ->sum(DB::raw('bkd.jumlah * bm.harga_beli'));

                // Keuntungan harian
                $profitData[] = $dailySales - $dailyCost - $dailyExpense;
            }
        } elseif ($filter_type === 'year') {
            // Data per bulan dalam tahun
            for ($month = 1; $month <= 12; $month++) {
                $startOfMonth = Carbon::createFromDate($filter_year, $month, 1)->startOfMonth();
                $endOfMonth = Carbon::createFromDate($filter_year, $month, 1)->endOfMonth();

                $period[] = $startOfMonth->format('M');

                // Penjualan bulanan
                $monthlySales = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
                })->sum(DB::raw('jumlah * harga'));
                $salesData[] = $monthlySales;

                // Pengeluaran bulanan
                $monthlyExpense = Pengeluaran::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('jumlah');
                $expenseData[] = $monthlyExpense;

                // Modal bulanan
                $monthlyCost = DB::table('barang_keluar_detail as bkd')
                    ->join('barang_masuk as bm', 'bkd.barang_masuk_id', '=', 'bm.id')
                    ->join('barang_keluar as bk', 'bkd.barang_keluar_id', '=', 'bk.id')
                    ->whereBetween('bk.tanggal', [$startOfMonth, $endOfMonth])
                    ->sum(DB::raw('bkd.jumlah * bm.harga_beli'));

                // Keuntungan bulanan
                $profitData[] = $monthlySales - $monthlyCost - $monthlyExpense;
            }
        } else {
            // Data untuk periode custom - kelompokkan per hari
            $startDate = Carbon::parse($start_date);
            $endDate = Carbon::parse($end_date);

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $currentDate = $date->format('Y-m-d');
                $period[] = $date->format('d/m');

                // Penjualan harian
                $dailySales = BarangKeluarDetail::whereHas('barangKeluar', function ($query) use ($currentDate) {
                    $query->whereDate('tanggal', $currentDate);
                })->sum(DB::raw('jumlah * harga'));
                $salesData[] = $dailySales;

                // Pengeluaran harian
                $dailyExpense = Pengeluaran::whereDate('tanggal', $currentDate)->sum('jumlah');
                $expenseData[] = $dailyExpense;

                // Modal harian
                $dailyCost = DB::table('barang_keluar_detail as bkd')
                    ->join('barang_masuk as bm', 'bkd.barang_masuk_id', '=', 'bm.id')
                    ->join('barang_keluar as bk', 'bkd.barang_keluar_id', '=', 'bk.id')
                    ->whereDate('bk.tanggal', $currentDate)
                    ->sum(DB::raw('bkd.jumlah * bm.harga_beli'));

                // Keuntungan harian
                $profitData[] = $dailySales - $dailyCost - $dailyExpense;
            }
        }

        // Data untuk chart barang (30 hari terakhir)
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

        return view('dashboard', compact(
            'totalBarang',
            'stokMinimal',
            'barangMasukHariIni',
            'barangKeluarHariIni',
            'barangMasukPeriode',
            'barangKeluarPeriode',
            'penjualanHariIni',
            'penjualanPeriode',
            'pengeluaranHariIni',
            'pengeluaranPeriode',
            'keuntunganHariIni',
            'keuntunganPeriode',
            'dailyMasuk',
            'dailyKeluar',
            'period',
            'salesData',
            'expenseData',
            'profitData',
            'filter_type',
            'filter_month',
            'filter_year',
            'start_date',
            'end_date'
        ));
    }
}