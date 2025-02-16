<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        // Query untuk mengambil data barang keluar dengan relasi yang diperlukan
        $query = BarangKeluar::with(['detail.barangMasuk.barang.satuan'])
            ->orderBy('tanggal', 'desc');

        // Filter berdasarkan tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
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

        // Jika request biasa, tampilkan view dengan data
        return view('pages.barang-keluar', compact('barangKeluar'));
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
                    'stok_ecer' => $sisaStok * ($bm->barang->satuan->konversi_ecer ?? 1),
                    'harga_jual' => $bm->harga_jual,
                    'harga_ecer' => $bm->harga_ecer,
                    'satuan_normal' => $bm->barang->satuan->nama,
                    'satuan_ecer' => $bm->barang->satuan->nama_ecer ?? $bm->barang->satuan->nama,
                    'konversi_ecer' => $bm->barang->satuan->konversi_ecer ?? 1
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
            // Buat satu record barang keluar untuk semua items
            $barangKeluar = BarangKeluar::create([
                'tanggal' => $validatedData['tanggal'],
                'keterangan' => $validatedData['keterangan']
            ]);

            $items = [];
            $totalNota = 0;

            // Loop untuk setiap item
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
                    $konversiEcer = $barangMasuk->barang->satuan->konversi_ecer ?? 1;
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

                // Simpan detail barang keluar
                $detail = BarangKeluarDetail::create([
                    'barang_keluar_id' => $barangKeluar->id,
                    'barang_masuk_id' => $barangMasuk->id,
                    'jumlah' => $jumlahNormal,
                    'harga' => $harga,
                    'tipe' => $item['tipe']
                ]);

                // Update stok barang masuk
                $barangMasuk->stok -= $jumlahNormal;
                $barangMasuk->save();

                // Tambahkan ke array untuk struk
                $items[] = [
                    'nama_barang' => $barangMasuk->barang->nama,
                    'jumlah' => number_format($item['jumlah'], 2, ',', '.'),
                    'satuan' => $item['tipe'] === 'normal' ?
                        $barangMasuk->barang->satuan->nama :
                        ($barangMasuk->barang->satuan->nama_ecer ?? $barangMasuk->barang->satuan->nama),
                    'harga' => number_format($harga, 2, ',', '.'),
                    'subtotal' => number_format($item['jumlah'] * $harga, 2, ',', '.')
                ];

                $totalNota += $item['jumlah'] * $harga;
            }

            DB::commit();

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'redirect' => route('barang-keluar.index'),
                'struk' => [
                    'tanggal' => date('d/m/Y H:i', strtotime($barangKeluar->tanggal)),
                    'items' => $items,
                    'total' => number_format($totalNota, 2, ',', '.')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
                $barangMasuk = $detail->barangMasuk;
                $barangMasuk->stok += $detail->jumlah;
                $barangMasuk->save();
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

            $totalKeseluruhan = $barangKeluar->detail->sum(function ($detail) {
                return $detail->jumlah * $detail->harga;
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
                'total_keseluruhan' => 'Rp ' . number_format($totalKeseluruhan, 2, ',', '.')
            ]);
        } catch (\Exception $e) {
            \Log::error('Cetak Struk Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}