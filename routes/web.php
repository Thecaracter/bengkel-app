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
    Route::get('/barang/{barang}/stock', [BarangController::class, 'getStock'])->name('barang.stock');

    // Barang Masuk - Define specific routes before resource route
    Route::get('/barang-masuk/search', [BarangMasukController::class, 'search'])->name('barang-masuk.search');
    Route::get('/barang-masuk/next-nomor-nota', [BarangMasukController::class, 'getNextNomorNota'])->name('barang-masuk.next-nomor-nota');
    Route::resource('barang-masuk', BarangMasukController::class);

    // Barang Keluar - Define specific routes before resource route
    Route::get('/barang-keluar/search-barang', [BarangKeluarController::class, 'loadBarangMasuk'])->name('barang-keluar.search-barang');
    Route::get('/barang-keluar/{barangKeluar}/cetak', [BarangKeluarController::class, 'cetakStruk'])->name('barang-keluar.cetak');
    Route::resource('barang-keluar', BarangKeluarController::class);

    // Admin only routes
    Route::resource('satuan', SatuanController::class);
    Route::resource('kategori', KategoriController::class);
    Route::resource('pengeluaran', PengeluaranController::class);
});

Route::group(['prefix' => 'debug', 'middleware' => 'auth'], function () {

    // Debug stok untuk barang tertentu
    Route::get('/stock/{barang}', function ($barangId) {
        try {
            $barang = \App\Models\Barang::with(['barangMasuk.barangKeluarDetails.barangKeluar'])->findOrFail($barangId);

            $totalMasuk = $barang->barangMasuk->sum('jumlah');
            $totalKeluar = 0;

            $batchDetails = [];
            foreach ($barang->barangMasuk as $bm) {
                $keluarBatch = $bm->barangKeluarDetails->sum('jumlah');
                $totalKeluar += $keluarBatch;
                $stokBatchSeharusnya = $bm->jumlah - $keluarBatch;

                $batchDetails[] = [
                    'id' => $bm->id,
                    'nomor_nota' => $bm->nomor_nota,
                    'tanggal' => $bm->tanggal->format('d/m/Y'),
                    'stok_masuk_awal' => (float) $bm->jumlah,
                    'stok_current' => (float) $bm->stok,
                    'stok_seharusnya' => (float) $stokBatchSeharusnya,
                    'total_keluar' => (float) $keluarBatch,
                    'batch_inconsistent' => $bm->stok != $stokBatchSeharusnya,
                    'keluar_details' => $bm->barangKeluarDetails->map(function ($detail) {
                        return [
                            'barang_keluar_id' => $detail->barang_keluar_id,
                            'tanggal' => $detail->barangKeluar->tanggal->format('d/m/Y H:i'),
                            'jumlah' => (float) $detail->jumlah,
                            'tipe' => $detail->tipe,
                            'harga' => (float) $detail->harga
                        ];
                    })
                ];
            }

            $stokSeharusnya = $totalMasuk - $totalKeluar;
            $totalInconsistent = $barang->stok != $stokSeharusnya;

            return response()->json([
                'barang_id' => $barang->id,
                'nama_barang' => $barang->nama,
                'stok_current' => (float) $barang->stok,
                'stok_minimal' => (float) $barang->stok_minimal,
                'total_masuk' => (float) $totalMasuk,
                'total_keluar' => (float) $totalKeluar,
                'stok_seharusnya' => (float) $stokSeharusnya,
                'selisih' => (float) ($barang->stok - $stokSeharusnya),
                'total_inconsistent' => $totalInconsistent,
                'batch_details' => $batchDetails,
                'batch_inconsistent_count' => collect($batchDetails)->where('batch_inconsistent', true)->count()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('debug.stock');

    // Fix stok untuk barang tertentu
    Route::get('/fix-stock/{barang}', function ($barangId) {
        try {
            \DB::beginTransaction();

            $barang = \App\Models\Barang::with(['barangMasuk.barangKeluarDetails'])->findOrFail($barangId);

            $totalMasuk = $barang->barangMasuk->sum('jumlah');
            $totalKeluar = 0;
            $batchFixed = [];

            // Fix each batch stock first
            foreach ($barang->barangMasuk as $bm) {
                $keluarBatch = $bm->barangKeluarDetails->sum('jumlah');
                $stokBatchSeharusnya = $bm->jumlah - $keluarBatch;
                $totalKeluar += $keluarBatch;

                if ($bm->stok != $stokBatchSeharusnya) {
                    $stokBatchLama = $bm->stok;
                    $bm->stok = $stokBatchSeharusnya;
                    $bm->save();

                    $batchFixed[] = [
                        'nomor_nota' => $bm->nomor_nota,
                        'old_stock' => (float) $stokBatchLama,
                        'new_stock' => (float) $stokBatchSeharusnya,
                        'difference' => (float) ($stokBatchSeharusnya - $stokBatchLama)
                    ];
                }
            }

            // Fix total stock
            $stokSeharusnya = $totalMasuk - $totalKeluar;
            $stokLama = $barang->stok;
            $totalFixed = false;

            if ($barang->stok != $stokSeharusnya) {
                $barang->stok = $stokSeharusnya;
                $barang->save();
                $totalFixed = true;
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'barang_id' => $barang->id,
                'nama_barang' => $barang->nama,
                'total_fixed' => $totalFixed,
                'old_total_stock' => (float) $stokLama,
                'new_total_stock' => (float) $stokSeharusnya,
                'total_difference' => (float) ($stokSeharusnya - $stokLama),
                'batch_fixed_count' => count($batchFixed),
                'batch_fixed' => $batchFixed
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('debug.fix-stock');

    // Test database transaction
    Route::get('/test-transaction', function () {
        try {
            \DB::beginTransaction();

            $barang = \App\Models\Barang::first();
            if (!$barang) {
                throw new \Exception('No barang found');
            }

            $stokAwal = $barang->stok;

            // Test update
            $barang->stok -= 0.01; // Kurangi sedikit untuk test
            $saved = $barang->save();

            \Log::info('Test transaction', [
                'barang_id' => $barang->id,
                'nama' => $barang->nama,
                'stok_awal' => $stokAwal,
                'stok_baru' => $barang->stok,
                'save_result' => $saved
            ]);

            // Kembalikan ke stok awal
            $barang->stok = $stokAwal;
            $barang->save();

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Database transaction test berhasil',
                'barang_tested' => $barang->nama,
                'stok_awal' => (float) $stokAwal,
                'test_completed' => true
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    })->name('debug.test-transaction');

    // Debug semua stok
    Route::get('/all-stock', function () {
        try {
            $barangs = \App\Models\Barang::with(['barangMasuk.barangKeluarDetails'])->get();
            $results = [];
            $totalInconsistent = 0;
            $batchInconsistent = 0;

            foreach ($barangs as $barang) {
                $totalMasuk = $barang->barangMasuk->sum('jumlah');
                $totalKeluar = 0;
                $batchProblems = 0;

                foreach ($barang->barangMasuk as $bm) {
                    $keluarBatch = $bm->barangKeluarDetails->sum('jumlah');
                    $stokBatchSeharusnya = $bm->jumlah - $keluarBatch;
                    $totalKeluar += $keluarBatch;

                    if ($bm->stok != $stokBatchSeharusnya) {
                        $batchProblems++;
                    }
                }

                $stokSeharusnya = $totalMasuk - $totalKeluar;
                $selisih = $barang->stok - $stokSeharusnya;
                $isInconsistent = $selisih != 0 || $batchProblems > 0;

                if ($isInconsistent) {
                    $totalInconsistent++;
                }

                $batchInconsistent += $batchProblems;

                $results[] = [
                    'id' => $barang->id,
                    'nama' => $barang->nama,
                    'stok_current' => (float) $barang->stok,
                    'stok_seharusnya' => (float) $stokSeharusnya,
                    'selisih_total' => (float) $selisih,
                    'batch_problems' => $batchProblems,
                    'is_inconsistent' => $isInconsistent,
                    'total_masuk' => (float) $totalMasuk,
                    'total_keluar' => (float) $totalKeluar
                ];
            }

            return response()->json([
                'total_barang' => $barangs->count(),
                'total_inconsistent' => $totalInconsistent,
                'batch_inconsistent' => $batchInconsistent,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('debug.all-stock');

    // Fix semua stok
    Route::post('/fix-all-stock', function () {
        try {
            \DB::beginTransaction();

            $barangs = \App\Models\Barang::with(['barangMasuk.barangKeluarDetails'])->get();
            $totalFixed = 0;
            $batchFixed = 0;
            $results = [];

            foreach ($barangs as $barang) {
                $totalMasuk = $barang->barangMasuk->sum('jumlah');
                $totalKeluar = 0;
                $batchFixedCount = 0;

                // Fix batches first
                foreach ($barang->barangMasuk as $bm) {
                    $keluarBatch = $bm->barangKeluarDetails->sum('jumlah');
                    $stokBatchSeharusnya = $bm->jumlah - $keluarBatch;
                    $totalKeluar += $keluarBatch;

                    if ($bm->stok != $stokBatchSeharusnya) {
                        $bm->stok = $stokBatchSeharusnya;
                        $bm->save();
                        $batchFixedCount++;
                    }
                }

                // Fix total
                $stokSeharusnya = $totalMasuk - $totalKeluar;
                $totalFixedThisItem = false;

                if ($barang->stok != $stokSeharusnya) {
                    $oldStock = $barang->stok;
                    $barang->stok = $stokSeharusnya;
                    $barang->save();
                    $totalFixedThisItem = true;
                    $totalFixed++;
                }

                $batchFixed += $batchFixedCount;

                if ($totalFixedThisItem || $batchFixedCount > 0) {
                    $results[] = [
                        'nama' => $barang->nama,
                        'total_fixed' => $totalFixedThisItem,
                        'batch_fixed' => $batchFixedCount
                    ];
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'total_items_fixed' => $totalFixed,
                'total_batches_fixed' => $batchFixed,
                'details' => $results
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('debug.fix-all-stock');
});

require __DIR__ . '/auth.php';