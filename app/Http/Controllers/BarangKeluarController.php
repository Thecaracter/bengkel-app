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
        // Query untuk mengambil data barang masuk dengan relasi yang diperlukan
        $query = BarangMasuk::with(['barang.satuan', 'barangKeluarDetails']);

        // Filter berdasarkan barcode
        if ($request->filled('barcode')) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('barcode', $request->barcode);
            });
        }

        // Filter berdasarkan pencarian
        else if ($request->filled('search')) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        // Ambil data dan hitung sisa stok
        $barangMasuk = $query->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($bm) {
                // Hitung sisa stok dengan pengecekan null
                $sisaStokKeluar = $bm->barangKeluarDetails ? $bm->barangKeluarDetails->sum('jumlah') : 0;
                $sisaStok = $bm->stok - $sisaStokKeluar;

                return [
                    'id' => $bm->id,
                    'barcode' => $bm->barang->barcode,
                    'nomor_nota' => $bm->nomor_nota,
                    'nama_barang' => $bm->barang->nama,
                    'bisa_ecer' => $bm->barang->bisa_ecer,
                    'stok' => $sisaStok,
                    'stok_ecer' => $sisaStok * ($bm->barang->satuan->konversi ?? 1),
                    'harga_jual' => $bm->harga_jual,
                    'harga_ecer' => $bm->harga_ecer,
                    'satuan_normal' => $bm->barang->satuan->nama,
                    'satuan_ecer' => $bm->barang->satuan->nama_ecer ?? $bm->barang->satuan->nama,
                    'konversi_ecer' => $bm->barang->satuan->konversi ?? 1
                ];
            })
            ->filter(function ($bm) {
                // Hanya tampilkan barang dengan stok > 0
                return $bm['stok'] > 0;
            })
            ->values();

        return response()->json($barangMasuk);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        DB::beginTransaction();
        try {
            $totalNota = 0;
            $items = [];

            // Validasi dan hitung total terlebih dahulu
            foreach ($validatedData['items'] as $item) {
                $barangMasuk = BarangMasuk::with('barang.satuan')->findOrFail($item['barang_masuk_id']);

                // Cek apakah barang bisa dijual ecer
                if ($item['tipe'] === 'ecer' && !$barangMasuk->barang->bisa_ecer) {
                    throw new \Exception('Barang ' . $barangMasuk->barang->nama . ' tidak bisa dijual ecer');
                }

                // Hitung sisa stok
                $sisaStok = $barangMasuk->stok - $barangMasuk->barangKeluarDetails->sum('jumlah');

                // Konversi jumlah jika tipe ecer
                $jumlahNormal = $item['jumlah'];
                if ($item['tipe'] === 'ecer') {
                    $konversiEcer = $barangMasuk->barang->satuan->konversi ?? 1;
                    $jumlahNormal = $item['jumlah'] / $konversiEcer;
                }

                // Cek stok cukup
                if ($jumlahNormal > $sisaStok) {
                    throw new \Exception('Stok ' . $barangMasuk->barang->nama . ' tidak mencukupi');
                }

                // Tentukan harga berdasarkan tipe
                $harga = $item['tipe'] === 'normal' ?
                    $barangMasuk->harga_jual :
                    $barangMasuk->harga_ecer;

                $totalNota += $item['jumlah'] * $harga;

                // Simpan data untuk proses detail
                $items[] = [
                    'barang_masuk' => $barangMasuk,
                    'jumlah_asli' => $item['jumlah'],
                    'jumlah_normal' => $jumlahNormal,
                    'tipe' => $item['tipe'],
                    'harga' => $harga
                ];
            }

            // Hitung kembalian
            $kembalian = $validatedData['jumlah_bayar'] - $totalNota;

            // Validasi pembayaran
            if ($validatedData['jumlah_bayar'] < $totalNota) {
                throw new \Exception('Jumlah bayar kurang dari total belanja. Kekurangan: ' . number_format($totalNota - $validatedData['jumlah_bayar'], 2, ',', '.'));
            }

            // Buat record barang keluar dengan payment info
            $barangKeluar = BarangKeluar::create([
                'tanggal' => $validatedData['tanggal'],
                'keterangan' => $validatedData['keterangan'],
                'total_harga' => $totalNota,
                'jumlah_bayar' => $validatedData['jumlah_bayar'],
                'kembalian' => $kembalian
            ]);

            // Simpan detail dan update stok
            $strukItems = [];
            foreach ($items as $item) {
                // Simpan detail barang keluar
                BarangKeluarDetail::create([
                    'barang_keluar_id' => $barangKeluar->id,
                    'barang_masuk_id' => $item['barang_masuk']->id,
                    'jumlah' => $item['jumlah_normal'], // Jumlah dalam satuan normal
                    'harga' => $item['harga'],
                    'tipe' => $item['tipe']
                ]);

                // Update stok barang
                $barang = $item['barang_masuk']->barang;
                $barang->stok -= $item['jumlah_normal'];
                $barang->save();

                // Tambahkan ke array untuk struk
                $strukItems[] = [
                    'nama_barang' => $item['barang_masuk']->barang->nama,
                    'jumlah' => number_format($item['jumlah_asli'], 2, ',', '.'),
                    'satuan' => $item['tipe'] === 'normal' ?
                        $item['barang_masuk']->barang->satuan->nama :
                        ($item['barang_masuk']->barang->satuan->nama_ecer ?? $item['barang_masuk']->barang->satuan->nama),
                    'harga' => number_format($item['harga'], 2, ',', '.'),
                    'total' => number_format($item['jumlah_asli'] * $item['harga'], 2, ',', '.')
                ];
            }

            DB::commit();

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
            \Log::error('Error in barang keluar store: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $barangKeluar = BarangKeluar::with('detail.barangMasuk')->findOrFail($id);

            // Kembalikan stok barang untuk setiap detail
            foreach ($barangKeluar->detail as $detail) {
                $barang = $detail->barangMasuk->barang;
                $barang->stok += $detail->jumlah;
                $barang->save();
            }

            // Hapus detail barang keluar
            $barangKeluar->detail()->delete();

            // Hapus barang keluar
            $barangKeluar->delete();

            DB::commit();
            return response()->json([
                'message' => 'Data barang keluar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function cetakStruk(BarangKeluar $barangKeluar)
    {
        try {
            // Load relasi dengan eager loading
            $barangKeluar->load([
                'detail.barangMasuk.barang'
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

                return [
                    'nama_barang' => $barang->nama ?? 'Barang Tidak Diketahui',
                    'jumlah' => number_format($detail->jumlah, 2, ',', '.'),
                    'tipe' => $detail->tipe === 'ecer' ? 'Ecer' : 'Normal',
                    'harga' => 'Rp ' . number_format($detail->harga, 2, ',', '.'),
                    'total' => 'Rp ' . number_format($detail->jumlah * $detail->harga, 2, ',', '.')
                ];
            });

            // Ambil nomor nota dari detail pertama
            $nomorNota = $barangKeluar->detail->first()
                ? optional($barangKeluar->detail->first()->barangMasuk)->nomor_nota
                : '-';

            return response()->json([
                'tanggal' => optional($barangKeluar->tanggal)->format('d/m/Y H:i') ?? '-',
                'nomor_nota' => $nomorNota,
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