<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    public function index()
    {
        $barang = Barang::orderBy('nama')->get();
        $barangMasuk = BarangMasuk::with(['barang'])
            ->orderBy('tanggal', 'desc')
            ->paginate(10);


        $nextNomorNota = $this->generateNomorNota();

        return view('pages.barang-masuk', compact('barangMasuk', 'barang', 'nextNomorNota'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_nota' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'barang_id' => 'required|exists:barang,id',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'harga_ecer' => 'nullable|numeric|min:0',
            'stok' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $validated['jumlah'] = $validated['stok'];

            $barangMasuk = BarangMasuk::create($validated);

            $barang = Barang::find($request->barang_id);
            $barang->stok += $request->stok;
            $barang->save();

            DB::commit();
            return response()->json([
                'message' => 'Data berhasil disimpan',
                'redirect' => route('barang-masuk.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(BarangMasuk $barangMasuk)
    {
        DB::beginTransaction();
        try {

            if ($barangMasuk->barangKeluarDetails->count() > 0) {
                return response()->json([
                    'error' => 'Data tidak dapat dihapus karena sudah ada transaksi barang keluar'
                ], 422);
            }

            $barang = $barangMasuk->barang;
            $barang->stok -= $barangMasuk->stok;
            $barang->save();

            $barangMasuk->delete();

            DB::commit();
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function search(Request $request)
    {
        $barangMasuk = BarangMasuk::with(['barang'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('nomor_nota', 'like', "%{$request->search}%")
                    ->orWhereHas('barang', function ($q) use ($request) {
                        $q->where('nama', 'like', "%{$request->search}%");
                    });
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(10);


        $nextNomorNota = $this->generateNomorNota();

        if ($request->ajax()) {
            return response()->json([
                'barangMasuk' => $barangMasuk,
                'nextNomorNota' => $nextNomorNota
            ]);
        }

        return view('pages.barang-masuk', compact('barangMasuk', 'nextNomorNota'));
    }

    public function searchBarang(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('barcode')) {
            $query->where('barcode', $request->barcode);
        } else if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }

        $barang = $query->with(['satuan', 'kategori'])->get();
        return response()->json($barang);
    }


    public function generateNomorNota()
    {

        $lastNota = BarangMasuk::where('nomor_nota', 'like', 'BM-%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastNota) {

            return 'BM-1';
        }


        $lastNumber = (int) substr($lastNota->nomor_nota, 3);


        $nextNumber = $lastNumber + 1;
        return 'BM-' . $nextNumber;
    }

    public function getNextNomorNota()
    {
        $nextNomorNota = $this->generateNomorNota();
        return response()->json(['nomor_nota' => $nextNomorNota]);
    }
}