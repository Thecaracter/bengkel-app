<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\PengeluaranController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master data routes
    Route::resources([
        'satuan' => SatuanController::class,
        'kategori' => KategoriController::class
    ]);

    // Barang routes
    Route::controller(BarangController::class)->group(function () {
        Route::get('barang', 'index')->name('barang.index');
        Route::post('barang', 'store')->name('barang.store');
        Route::get('barang/{barang}', 'show')->name('barang.show');
        Route::put('barang/{barang}', 'update')->name('barang.update');
        Route::delete('barang/{barang}', 'destroy')->name('barang.destroy');
        Route::get('barang/{barang}/stock', 'getStock')->name('barang.stock');
    });

    // Barang Masuk Routes
    Route::controller(BarangMasukController::class)->group(function () {
        Route::get('barang-masuk', 'index')->name('barang-masuk.index');
        Route::post('barang-masuk', 'store')->name('barang-masuk.store');
        Route::delete('barang-masuk/{barangMasuk}', 'destroy')->name('barang-masuk.destroy');
        Route::get('barang-masuk/search', 'search')->name('barang-masuk.search');
        Route::get('barang-masuk/search-barang', 'searchBarang')->name('barang-masuk.search-barang');
        Route::get('barang-masuk/next-nomor-nota', 'getNextNomorNota')->name('barang-masuk.next-nomor-nota');
    });

    // Barang Keluar Routes
    Route::controller(BarangKeluarController::class)->prefix('barang-keluar')->name('barang-keluar.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{barangKeluar}', 'destroy')->name('destroy');
        Route::get('/search-barang', 'loadBarangMasuk')->name('search');
        Route::get('/{barangKeluar}/cetak', 'cetakStruk')->name('cetak');
    });

    // Pengeluaran Routes
    Route::controller(PengeluaranController::class)->group(function () {
        Route::get('pengeluaran', 'index')->name('pengeluaran.index');
        Route::post('pengeluaran', 'store')->name('pengeluaran.store');
        Route::get('pengeluaran/{pengeluaran}', 'show')->name('pengeluaran.show');
        Route::put('pengeluaran/{pengeluaran}', 'update')->name('pengeluaran.update');
        Route::delete('pengeluaran/{pengeluaran}', 'destroy')->name('pengeluaran.destroy');
    });
});

require __DIR__ . '/auth.php';