<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        // Query untuk mengambil data barang keluar dengan relasi yang diperlukan
        $query = BarangKeluar::with(['detail.barangMasuk.barang.satuan'])
            ->orderBy('tanggal', 'desc');

        // Default ke semua data jika tidak ada filter
        $startDate = '';
        $endDate = '';

        // Hanya apply filter jika user memang set filter
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->whereDate('tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('tanggal', '<=', $endDate);
            }
        }

        // Jika ada parameter reset_filter, hapus semua filter
        if ($request->has('reset_filter')) {
            // Query sudah reset, tidak perlu filter apapun
            $startDate = '';
            $endDate = '';
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('detail.barangMasuk', function ($q) use ($request) {
                    $q->where('nomor_nota', 'like', "%{$request->search}%")
                        ->orWhereHas('barang', function ($q) use ($request) {
                            $q->where('nama', 'like', "%{$request->search}%");
                        });
                });
            });
        }

        // Paginasi hasil query
        $barangKeluar = $query->paginate(10);

        // Jika request JSON, kembalikan data dalam format JSON
        if ($request->wantsJson()) {
            return response()->json($barangKeluar);
        }

        // Jika request biasa, tampilkan view dengan data tanpa default filter
        return view('pages.barang-keluar', compact('barangKeluar'))
            ->with('defaultStartDate', $startDate)
            ->with('defaultEndDate', $endDate);
    }

    public function loadBarangMasuk(Request $request)
    {
        try {
            // Validasi input
            if (!$request->filled('search') || strlen(trim($request->search)) < 1) {
                return response()->json([]);
            }

            $searchTerm = trim($request->search);
            \Log::info('Searching for barang', ['search_term' => $searchTerm]);

            // Query untuk mengambil data barang masuk dengan relasi yang diperlukan
            $query = BarangMasuk::with(['barang.satuan', 'barangKeluarDetails'])
                ->whereHas('barang', function ($q) use ($searchTerm) {
                    $q->where('nama', 'like', "%{$searchTerm}%")
                        ->orWhere('barcode', 'like', "%{$searchTerm}%")
                        ->orWhere('barcode', '=', $searchTerm);
                });

            // Ambil data dan hitung sisa stok per batch
            $barangMasuk = $query->orderBy('tanggal', 'desc')
                ->get()
                ->map(function ($bm) {
                    try {
                        // Pastikan relasi ada dan tidak null
                        if (!$bm || !$bm->barang) {
                            \Log::warning('BarangMasuk or Barang is null', ['id' => $bm->id ?? 'unknown']);
                            return null;
                        }

                        // Hitung sisa stok batch dengan pengecekan null
                        $sisaStokKeluar = $bm->barangKeluarDetails ? $bm->barangKeluarDetails->sum('jumlah') : 0;
                        $sisaStokBatch = $bm->stok - $sisaStokKeluar;

                        \Log::info('Batch stock calculation', [
                            'batch_id' => $bm->id,
                            'nomor_nota' => $bm->nomor_nota,
                            'stok_awal' => $bm->stok,
                            'sudah_keluar' => $sisaStokKeluar,
                            'sisa_batch' => $sisaStokBatch
                        ]);

                        // Skip jika stok batch habis
                        if ($sisaStokBatch <= 0) {
                            \Log::info('Skipping batch with zero stock', ['batch_id' => $bm->id]);
                            return null;
                        }

                        // Ambil data satuan dengan fallback
                        $satuan = $bm->barang->satuan;
                        $konversi = $satuan->konversi ?? 1;

                        $result = [
                            'id' => $bm->id,
                            'barcode' => $bm->barang->barcode ?? '',
                            'nomor_nota' => $bm->nomor_nota ?? '',
                            'nama_barang' => $bm->barang->nama ?? 'Nama tidak diketahui',
                            'batch_info' => "Batch {$bm->nomor_nota} - Sisa: {$sisaStokBatch}",
                            'bisa_ecer' => (bool) ($bm->barang->bisa_ecer ?? false),
                            'stok' => (float) $sisaStokBatch, // Stok yang tersedia dari batch ini
                            'stok_ecer' => (float) ($sisaStokBatch * $konversi),
                            'harga_jual' => (float) ($bm->harga_jual ?? 0),
                            'harga_ecer' => (float) ($bm->harga_ecer ?? 0),
                            'satuan_normal' => $satuan->nama ?? 'pcs',
                            'satuan_ecer' => 'pcs', // Hardcode karena tidak ada kolom nama_ecer
                            'konversi_ecer' => (int) $konversi,
                            'tanggal_masuk' => $bm->tanggal->format('d/m/Y')
                        ];

                        \Log::info('Batch result', $result);
                        return $result;

                    } catch (\Exception $e) {
                        \Log::error('Error mapping BarangMasuk', [
                            'id' => $bm->id ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        return null;
                    }
                })
                ->filter(function ($bm) {
                    // Filter null values
                    return $bm !== null;
                })
                ->values();

            \Log::info('Search barang completed', [
                'search_term' => $searchTerm,
                'results_count' => $barangMasuk->count(),
                'results_summary' => $barangMasuk->map(function ($item) {
                    return [
                        'batch' => $item['nomor_nota'],
                        'nama' => $item['nama_barang'],
                        'stok' => $item['stok']
                    ];
                })
            ]);

            return response()->json($barangMasuk);

        } catch (\Exception $e) {
            \Log::error('Error in loadBarangMasuk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat mencari barang',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'jumlah_bayar' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.barang_masuk_id' => 'required|exists:barang_masuk,id',
            'items.*.jumlah' => 'required|numeric|min:0.01',
            'items.*.tipe' => 'required|in:normal,ecer'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        DB::beginTransaction();
        try {
            \Log::info('=== STARTING BARANG KELUAR TRANSACTION ===', [
                'items_count' => count($validatedData['items']),
                'user' => auth()->user()->name ?? 'unknown',
                'request_data' => $validatedData
            ]);

            $totalNota = 0;
            $items = [];

            // STEP 1: Validasi dan hitung total terlebih dahulu
            foreach ($validatedData['items'] as $index => $item) {
                $barangMasuk = BarangMasuk::with('barang.satuan')->findOrFail($item['barang_masuk_id']);

                \Log::info("Validating item {$index}", [
                    'barang_masuk_id' => $barangMasuk->id,
                    'nomor_nota' => $barangMasuk->nomor_nota,
                    'barang_nama' => $barangMasuk->barang->nama,
                    'stok_batch_current' => $barangMasuk->stok,
                    'stok_barang_total' => $barangMasuk->barang->stok,
                    'requested_qty' => $item['jumlah'],
                    'requested_type' => $item['tipe']
                ]);

                // Cek apakah barang bisa dijual ecer
                if ($item['tipe'] === 'ecer' && !$barangMasuk->barang->bisa_ecer) {
                    throw new \Exception('Barang ' . $barangMasuk->barang->nama . ' tidak bisa dijual ecer');
                }

                // Hitung sisa stok dari batch ini (barang_masuk)
                $sudahKeluarBatch = $barangMasuk->barangKeluarDetails->sum('jumlah');
                $sisaStokBatch = $barangMasuk->stok - $sudahKeluarBatch;

                \Log::info('Stock calculation for batch', [
                    'nomor_nota' => $barangMasuk->nomor_nota,
                    'stok_awal_batch' => $barangMasuk->stok,
                    'sudah_keluar_batch' => $sudahKeluarBatch,
                    'sisa_stok_batch' => $sisaStokBatch
                ]);

                // Konversi jumlah jika tipe ecer
                $jumlahNormal = $item['jumlah'];
                if ($item['tipe'] === 'ecer') {
                    $konversiEcer = $barangMasuk->barang->satuan->konversi ?? 1;
                    $jumlahNormal = $item['jumlah'] / $konversiEcer;

                    \Log::info('Ecer conversion', [
                        'jumlah_ecer_input' => $item['jumlah'],
                        'konversi' => $konversiEcer,
                        'jumlah_normal_converted' => $jumlahNormal
                    ]);
                }

                // Cek stok batch cukup
                if ($jumlahNormal > $sisaStokBatch) {
                    $errorMsg = "Stok batch {$barangMasuk->nomor_nota} untuk {$barangMasuk->barang->nama} tidak mencukupi. Diminta: {$jumlahNormal}, Sisa: {$sisaStokBatch}";
                    \Log::error('Insufficient batch stock', [
                        'error' => $errorMsg,
                        'batch' => $barangMasuk->nomor_nota,
                        'requested' => $jumlahNormal,
                        'available' => $sisaStokBatch
                    ]);
                    throw new \Exception($errorMsg);
                }

                // Tentukan harga berdasarkan tipe
                $harga = $item['tipe'] === 'normal' ?
                    $barangMasuk->harga_jual :
                    $barangMasuk->harga_ecer;

                $subtotal = $item['jumlah'] * $harga;
                $totalNota += $subtotal;

                \Log::info('Item pricing', [
                    'tipe' => $item['tipe'],
                    'harga_satuan' => $harga,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal
                ]);

                // Simpan data untuk proses detail
                $items[] = [
                    'barang_masuk' => $barangMasuk,
                    'jumlah_asli' => $item['jumlah'],
                    'jumlah_normal' => $jumlahNormal,
                    'tipe' => $item['tipe'],
                    'harga' => $harga,
                    'subtotal' => $subtotal
                ];
            }

            // STEP 2: Hitung kembalian dan validasi pembayaran
            $kembalian = $validatedData['jumlah_bayar'] - $totalNota;

            if ($validatedData['jumlah_bayar'] < $totalNota) {
                $errorMsg = 'Jumlah bayar kurang dari total belanja. Total: ' . number_format($totalNota, 2, ',', '.') . ', Bayar: ' . number_format($validatedData['jumlah_bayar'], 2, ',', '.') . ', Kekurangan: ' . number_format($totalNota - $validatedData['jumlah_bayar'], 2, ',', '.');
                \Log::error('Insufficient payment', ['error' => $errorMsg]);
                throw new \Exception($errorMsg);
            }

            \Log::info('Payment calculation', [
                'total_nota' => $totalNota,
                'jumlah_bayar' => $validatedData['jumlah_bayar'],
                'kembalian' => $kembalian
            ]);

            // STEP 3: Buat record barang keluar dengan payment info
            $barangKeluar = BarangKeluar::create([
                'tanggal' => $validatedData['tanggal'],
                'keterangan' => $validatedData['keterangan'],
                'total_harga' => $totalNota,
                'jumlah_bayar' => $validatedData['jumlah_bayar'],
                'kembalian' => $kembalian
            ]);

            \Log::info('Barang keluar created', ['id' => $barangKeluar->id]);

            // STEP 4: Simpan detail dan update stok (KEDUA TABEL)
            $strukItems = [];
            foreach ($items as $index => $item) {
                \Log::info("Processing detail {$index}", [
                    'barang_keluar_id' => $barangKeluar->id,
                    'barang_masuk_id' => $item['barang_masuk']->id,
                    'nomor_nota' => $item['barang_masuk']->nomor_nota,
                    'jumlah_normal' => $item['jumlah_normal']
                ]);

                // Simpan detail barang keluar
                $detail = BarangKeluarDetail::create([
                    'barang_keluar_id' => $barangKeluar->id,
                    'barang_masuk_id' => $item['barang_masuk']->id,
                    'jumlah' => $item['jumlah_normal'], // Jumlah dalam satuan normal
                    'harga' => $item['harga'],
                    'tipe' => $item['tipe']
                ]);

                \Log::info('Detail created', ['detail_id' => $detail->id]);

                // ⭐ UPDATE 1: Kurangi stok di tabel barang_masuk (stok per batch)
                $barangMasukToUpdate = $item['barang_masuk'];
                $stokBatchSebelum = $barangMasukToUpdate->stok;

                $barangMasukToUpdate->stok -= $item['jumlah_normal'];
                $batchSaved = $barangMasukToUpdate->save();

                \Log::info('Batch stock updated', [
                    'barang_masuk_id' => $barangMasukToUpdate->id,
                    'nomor_nota' => $barangMasukToUpdate->nomor_nota,
                    'stok_sebelum' => $stokBatchSebelum,
                    'stok_setelah' => $barangMasukToUpdate->stok,
                    'jumlah_keluar' => $item['jumlah_normal'],
                    'save_result' => $batchSaved
                ]);

                // ⭐ UPDATE 2: Kurangi stok di tabel barang (stok total keseluruhan)
                $barang = $barangMasukToUpdate->barang;
                $stokTotalSebelum = $barang->stok;

                $barang->stok -= $item['jumlah_normal'];
                $totalSaved = $barang->save();

                \Log::info('Total stock updated', [
                    'barang_id' => $barang->id,
                    'nama_barang' => $barang->nama,
                    'stok_sebelum' => $stokTotalSebelum,
                    'stok_setelah' => $barang->stok,
                    'jumlah_keluar' => $item['jumlah_normal'],
                    'save_result' => $totalSaved
                ]);

                // Verifikasi update berhasil
                $barangFresh = $barang->fresh();
                $batchFresh = $barangMasukToUpdate->fresh();

                \Log::info('Post-update verification', [
                    'barang_stok_fresh' => $barangFresh->stok,
                    'batch_stok_fresh' => $batchFresh->stok
                ]);

                // Tambahkan ke array untuk struk
                $strukItems[] = [
                    'nama_barang' => $barang->nama,
                    'batch_info' => "Batch {$barangMasukToUpdate->nomor_nota}",
                    'jumlah' => number_format($item['jumlah_asli'], 2, ',', '.'),
                    'satuan' => $item['tipe'] === 'normal' ?
                        $barang->satuan->nama :
                        'pcs', // Hardcode karena tidak ada nama_ecer
                    'harga' => number_format($item['harga'], 2, ',', '.'),
                    'total' => number_format($item['subtotal'], 2, ',', '.')
                ];
            }

            DB::commit();

            \Log::info('=== TRANSACTION COMMITTED SUCCESSFULLY ===', [
                'barang_keluar_id' => $barangKeluar->id,
                'total_items' => count($items),
                'total_amount' => $totalNota,
                'kembalian' => $kembalian
            ]);

            return response()->json([
                'message' => 'Transaksi berhasil disimpan',
                'redirect' => route('barang-keluar.index'),
                'struk' => [
                    'tanggal' => Carbon::parse($barangKeluar->tanggal)->format('d/m/Y H:i'),
                    'items' => $strukItems,
                    'total_keseluruhan' => 'Rp ' . number_format($totalNota, 2, ',', '.'),
                    'jumlah_bayar' => 'Rp ' . number_format($validatedData['jumlah_bayar'], 2, ',', '.'),
                    'kembalian' => 'Rp ' . number_format($kembalian, 2, ',', '.')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('=== TRANSACTION FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $barangKeluar = BarangKeluar::with('detail.barangMasuk.barang')->findOrFail($id);

            \Log::info('=== DELETING BARANG KELUAR ===', [
                'barang_keluar_id' => $id,
                'detail_count' => $barangKeluar->detail->count(),
                'tanggal' => $barangKeluar->tanggal,
                'total_harga' => $barangKeluar->total_harga
            ]);

            // ⭐ PENTING: Kembalikan stok untuk setiap detail (KEDUA TABEL)
            foreach ($barangKeluar->detail as $detail) {
                // Kembalikan stok ke batch (barang_masuk)
                $barangMasuk = $detail->barangMasuk;
                $stokBatchSebelum = $barangMasuk->stok;

                $barangMasuk->stok += $detail->jumlah; // Tambah kembali stok batch
                $barangMasuk->save();

                \Log::info('Batch stock restored', [
                    'barang_masuk_id' => $barangMasuk->id,
                    'nomor_nota' => $barangMasuk->nomor_nota,
                    'stok_sebelum' => $stokBatchSebelum,
                    'stok_setelah' => $barangMasuk->stok,
                    'jumlah_dikembalikan' => $detail->jumlah
                ]);

                // Kembalikan stok ke total (barang)
                $barang = $barangMasuk->barang;
                $stokTotalSebelum = $barang->stok;

                $barang->stok += $detail->jumlah; // Tambah kembali stok total
                $barang->save();

                \Log::info('Total stock restored', [
                    'barang_id' => $barang->id,
                    'nama_barang' => $barang->nama,
                    'stok_sebelum' => $stokTotalSebelum,
                    'stok_setelah' => $barang->stok,
                    'jumlah_dikembalikan' => $detail->jumlah
                ]);
            }

            // Hapus detail barang keluar
            $barangKeluar->detail()->delete();

            // Hapus barang keluar
            $barangKeluar->delete();

            DB::commit();

            \Log::info('=== DELETION COMPLETED ===');

            return response()->json([
                'message' => 'Data barang keluar berhasil dihapus dan stok dikembalikan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error deleting barang keluar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function cetakStruk(BarangKeluar $barangKeluar)
    {
        try {
            // Load relasi dengan eager loading
            $barangKeluar->load([
                'detail.barangMasuk.barang.satuan'
            ]);

            // Pastikan detail tidak kosong
            if ($barangKeluar->detail->isEmpty()) {
                return response()->json([
                    'error' => 'Tidak ada detail barang keluar'
                ], 404);
            }

            $items = $barangKeluar->detail->map(function ($detail) {
                // Pastikan relasi tidak null
                $barangMasuk = optional($detail->barangMasuk);
                $barang = optional($barangMasuk->barang);
                $satuan = optional($barang->satuan);

                return [
                    'nama_barang' => $barang->nama ?? 'Barang Tidak Diketahui',
                    'batch_info' => "Batch {$barangMasuk->nomor_nota}",
                    'jumlah' => number_format($detail->jumlah, 2, ',', '.'),
                    'tipe' => $detail->tipe === 'ecer' ? 'Ecer' : 'Normal',
                    'harga' => 'Rp ' . number_format($detail->harga, 2, ',', '.'),
                    'total' => 'Rp ' . number_format($detail->jumlah * $detail->harga, 2, ',', '.')
                ];
            });

            return response()->json([
                'tanggal' => optional($barangKeluar->tanggal)->format('d/m/Y H:i') ?? '-',
                'keterangan' => $barangKeluar->keterangan ?? '-',
                'items' => $items,
                'total_keseluruhan' => 'Rp ' . number_format($barangKeluar->total_harga, 2, ',', '.'),
                'jumlah_bayar' => 'Rp ' . number_format($barangKeluar->jumlah_bayar, 2, ',', '.'),
                'kembalian' => 'Rp ' . number_format($barangKeluar->kembalian, 2, ',', '.')
            ]);
        } catch (\Exception $e) {
            \Log::error('Cetak Struk Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}