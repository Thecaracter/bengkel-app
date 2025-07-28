<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\PengeluaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Barang (accessible by both admin and user)
    Route::resource('barang', BarangController::class);
    Route::get('/barang/{barang}/stock', [BarangController::class, 'getStock']);

    // Barang Masuk endpoints for adding stock and history
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
    Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
    Route::get('/barang-masuk/search', [BarangMasukController::class, 'search']);
    Route::get('/barang-masuk/next-nomor-nota', [BarangMasukController::class, 'getNextNomorNota']);
    Route::delete('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');

    // Barang Keluar (accessible by both admin and user)
    Route::resource('barang-keluar', BarangKeluarController::class);
    Route::get('/barang-keluar/search-barang', [BarangKeluarController::class, 'loadBarangMasuk']);
    Route::get('/barang-keluar/{barangKeluar}/cetak', [BarangKeluarController::class, 'cetakStruk']);

    // Admin only routes (check in navigation/controller)
    Route::resource('satuan', SatuanController::class);
    Route::resource('kategori', KategoriController::class);
    Route::resource('pengeluaran', PengeluaranController::class);

    // Remove duplicate routes since they're already defined above
});

require __DIR__ . '/auth.php';