<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluarDetail;

class DebugStockCommand extends Command
{
    protected $signature = 'debug:stock {barang_id?} {--all} {--fix}';
    protected $description = 'Debug stock inconsistencies';

    public function handle()
    {
        if ($this->option('all')) {
            $this->debugAllStock();
        } else {
            $barangId = $this->argument('barang_id');
            if ($barangId) {
                $this->debugSingleStock($barangId);
            } else {
                $this->error('Please provide barang_id or use --all option');
                $this->info('Usage: php artisan debug:stock {barang_id} or php artisan debug:stock --all');
            }
        }
    }

    private function debugSingleStock($barangId)
    {
        try {
            $barang = Barang::with(['barangMasuk.barangKeluarDetails.barangKeluar'])->findOrFail($barangId);

            $this->info("=== DEBUG STOCK FOR: {$barang->nama} (ID: {$barang->id}) ===");
            $this->info("Current Total Stock: {$barang->stok}");
            $this->info("Minimal Stock: {$barang->stok_minimal}");

            $totalMasuk = 0;
            $totalKeluar = 0;
            $batchInconsistencies = [];

            $this->info("\n--- BATCH DETAILS ---");
            foreach ($barang->barangMasuk as $bm) {
                $keluarBatch = $bm->barangKeluarDetails->sum('jumlah');
                $stokBatchSeharusnya = $bm->jumlah - $keluarBatch;
                $batchInconsistent = $bm->stok != $stokBatchSeharusnya;

                $totalMasuk += $bm->jumlah;
                $totalKeluar += $keluarBatch;

                $status = $batchInconsistent ? ' ❌ INCONSISTENT' : ' ✅ OK';
                $this->line("Batch {$bm->nomor_nota} ({$bm->tanggal->format('d/m/Y')}){$status}");
                $this->line("  Masuk: {$bm->jumlah} | Keluar: {$keluarBatch} | Current: {$bm->stok} | Should be: {$stokBatchSeharusnya}");

                if ($batchInconsistent) {
                    $batchInconsistencies[] = [
                        'batch' => $bm,
                        'should_be' => $stokBatchSeharusnya
                    ];
                }

                // Detail transaksi keluar
                if ($bm->barangKeluarDetails->count() > 0) {
                    $this->line("  Keluar details:");
                    foreach ($bm->barangKeluarDetails as $detail) {
                        $tanggal = $detail->barangKeluar->tanggal->format('d/m/Y H:i');
                        $this->line("    - {$detail->jumlah} ({$detail->tipe}) pada {$tanggal}");
                    }
                }
            }

            $stokTotalSeharusnya = $totalMasuk - $totalKeluar;
            $totalInconsistent = $barang->stok != $stokTotalSeharusnya;
            $selisih = $barang->stok - $stokTotalSeharusnya;

            $this->info("\n--- SUMMARY ---");
            $this->info("Total Masuk: {$totalMasuk}");
            $this->info("Total Keluar: {$totalKeluar}");
            $this->info("Total Stock Should Be: {$stokTotalSeharusnya}");
            $this->info("Total Stock Current: {$barang->stok}");

            if ($totalInconsistent || !empty($batchInconsistencies)) {
                $this->error("\n🚨 INCONSISTENCIES DETECTED!");

                if ($totalInconsistent) {
                    $this->error("Total Stock Difference: {$selisih}");
                }

                if (!empty($batchInconsistencies)) {
                    $this->error("Batch Inconsistencies: " . count($batchInconsistencies));
                }

                if ($this->option('fix') || $this->confirm('Fix these inconsistencies?')) {
                    $this->info("\n🔧 FIXING INCONSISTENCIES...");

                    // Fix batch stocks
                    foreach ($batchInconsistencies as $item) {
                        $batch = $item['batch'];
                        $oldStock = $batch->stok;
                        $batch->stok = $item['should_be'];
                        $batch->save();

                        $this->info("Fixed batch {$batch->nomor_nota}: {$oldStock} → {$item['should_be']}");
                    }

                    // Fix total stock
                    if ($totalInconsistent) {
                        $oldTotalStock = $barang->stok;
                        $barang->stok = $stokTotalSeharusnya;
                        $barang->save();

                        $this->info("Fixed total stock: {$oldTotalStock} → {$stokTotalSeharusnya}");
                    }

                    $this->info("✅ All inconsistencies fixed!");
                }
            } else {
                $this->info("✅ All stocks are consistent!");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    private function debugAllStock()
    {
        $this->info("=== DEBUG ALL STOCK ===");

        $barangs = Barang::with(['barangMasuk.barangKeluarDetails'])->get();
        $inconsistencies = [];
        $batchInconsistencies = [];

        foreach ($barangs as $barang) {
            $totalMasuk = $barang->barangMasuk->sum('jumlah');
            $totalKeluar = 0;

            // Check each batch
            foreach ($barang->barangMasuk as $bm) {
                $keluarBatch = $bm->barangKeluarDetails->sum('jumlah');
                $stokBatchSeharusnya = $bm->jumlah - $keluarBatch;
                $totalKeluar += $keluarBatch;

                if ($bm->stok != $stokBatchSeharusnya) {
                    $batchInconsistencies[] = [
                        'barang_nama' => $barang->nama,
                        'batch_nota' => $bm->nomor_nota,
                        'current_batch_stock' => $bm->stok,
                        'should_be_batch_stock' => $stokBatchSeharusnya,
                        'batch_difference' => $bm->stok - $stokBatchSeharusnya,
                        'batch_id' => $bm->id
                    ];
                }
            }

            $stokSeharusnya = $totalMasuk - $totalKeluar;
            $selisih = $barang->stok - $stokSeharusnya;

            if ($selisih != 0) {
                $inconsistencies[] = [
                    'id' => $barang->id,
                    'nama' => $barang->nama,
                    'current' => $barang->stok,
                    'should_be' => $stokSeharusnya,
                    'difference' => $selisih
                ];
            }
        }

        // Display results
        if (empty($inconsistencies) && empty($batchInconsistencies)) {
            $this->info("✅ All stocks (total and batch) are consistent!");
        } else {
            if (!empty($inconsistencies)) {
                $this->error("Found " . count($inconsistencies) . " total stock inconsistencies:");
                $headers = ['ID', 'Nama Barang', 'Current Stock', 'Should Be', 'Difference'];
                $this->table($headers, $inconsistencies);
            }

            if (!empty($batchInconsistencies)) {
                $this->error("\nFound " . count($batchInconsistencies) . " batch stock inconsistencies:");
                $batchHeaders = ['Barang', 'Batch Nota', 'Current Batch', 'Should Be', 'Difference'];
                $this->table($batchHeaders, array_map(function ($item) {
                    return [
                        $item['barang_nama'],
                        $item['batch_nota'],
                        $item['current_batch_stock'],
                        $item['should_be_batch_stock'],
                        $item['batch_difference']
                    ];
                }, $batchInconsistencies));
            }

            if ($this->option('fix') || $this->confirm('Fix all inconsistencies?')) {
                $this->info("\n🔧 FIXING ALL INCONSISTENCIES...");

                // Fix total stock inconsistencies
                foreach ($inconsistencies as $item) {
                    $barang = Barang::find($item['id']);
                    $barang->stok = $item['should_be'];
                    $barang->save();
                    $this->info("Fixed total stock for {$item['nama']}: {$item['current']} → {$item['should_be']}");
                }

                // Fix batch inconsistencies
                foreach ($batchInconsistencies as $item) {
                    $barangMasuk = BarangMasuk::find($item['batch_id']);
                    $barangMasuk->stok = $item['should_be_batch_stock'];
                    $barangMasuk->save();
                    $this->info("Fixed batch {$item['batch_nota']}: {$item['current_batch_stock']} → {$item['should_be_batch_stock']}");
                }

                $this->info("✅ All inconsistencies fixed!");
            }
        }

        $this->info("\n📊 SUMMARY:");
        $this->info("Total Barang: " . $barangs->count());
        $this->info("Total Stock Inconsistencies: " . count($inconsistencies));
        $this->info("Batch Stock Inconsistencies: " . count($batchInconsistencies));
    }
}