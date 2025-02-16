<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Kategori;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Barang::with(['kategori', 'satuan', 'barangMasuk']);

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('nama', 'like', "%{$request->search}%")
                        ->orWhere('barcode', 'like', "%{$request->search}%")
                        ->orWhereHas('kategori', function ($q) use ($request) {
                            $q->where('nama', 'like', "%{$request->search}%");
                        })
                        ->orWhereHas('satuan', function ($q) use ($request) {
                            $q->where('nama', 'like', "%{$request->search}%");
                        });
                });
            }

            $barangs = $query->latest()->get();
            $kategori = Kategori::all();
            $satuan = Satuan::all();

            if ($request->wantsJson()) {
                return response()->json($barangs);
            }

            return view('pages.barang', compact('barangs', 'kategori', 'satuan'));

        } catch (Exception $e) {
            Log::error('Error loading barang data', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Terjadi kesalahan saat memuat data'], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'barcode' => 'nullable|string|unique:barang',
                'kategori_id' => 'required|exists:kategori,id',
                'satuan_id' => 'required|exists:satuan,id',
                'stok_minimal' => 'required|numeric|min:0',
                'bisa_ecer' => 'boolean'
            ]);

            $validated['bisa_ecer'] = $request->boolean('bisa_ecer');

            Log::info('Creating new barang', [
                'user' => auth()->user()->name,
                'data' => $validated
            ]);

            Barang::create($validated);
            DB::commit();

            return response()->json(['message' => 'Barang berhasil ditambahkan']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating barang', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name
            ]);

            return response()->json(['message' => 'Gagal menambahkan barang'], 500);
        }
    }

    public function show(Barang $barang)
    {
        try {
            $barang->load([
                'kategori',
                'satuan',
                'barangMasuk' => function ($q) {
                    $q->orderBy('tanggal', 'desc');
                }
            ]);

            return response()->json($barang);
        } catch (Exception $e) {
            Log::error('Error showing barang', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name,
                'barang_id' => $barang->id
            ]);

            return response()->json(['message' => 'Gagal memuat data barang'], 500);
        }
    }

    public function update(Request $request, Barang $barang)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'barcode' => ['nullable', 'string', Rule::unique('barang')->ignore($barang->id)],
                'kategori_id' => 'required|exists:kategori,id',
                'satuan_id' => 'required|exists:satuan,id',
                'stok_minimal' => 'required|numeric|min:0',
                'bisa_ecer' => 'boolean'
            ]);

            $validated['bisa_ecer'] = $request->boolean('bisa_ecer');

            Log::info('Updating barang', [
                'user' => auth()->user()->name,
                'barang_id' => $barang->id,
                'old_data' => $barang->toArray(),
                'new_data' => $validated
            ]);

            $barang->update($validated);
            DB::commit();

            return response()->json(['message' => 'Barang berhasil diperbarui']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating barang', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name,
                'barang_id' => $barang->id
            ]);

            return response()->json(['message' => 'Gagal memperbarui barang'], 500);
        }
    }

    public function destroy(Barang $barang)
    {
        try {
            DB::beginTransaction();

            if ($barang->barangMasuk()->exists()) {
                throw new Exception('Barang sudah digunakan dalam transaksi');
            }

            Log::info('Deleting barang', [
                'user' => auth()->user()->name,
                'barang_id' => $barang->id,
                'data' => $barang->toArray()
            ]);

            $barang->delete();
            DB::commit();

            return response()->json(['message' => 'Barang berhasil dihapus']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting barang', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name,
                'barang_id' => $barang->id
            ]);

            return response()->json([
                'message' => $e->getMessage() ?: 'Gagal menghapus barang'
            ], 422);
        }
    }

    public function getStock(Barang $barang)
    {
        try {
            $stocks = BarangMasuk::where('barang_id', $barang->id)
                ->select('tanggal', 'nomor_nota', 'stok', 'harga_beli', 'harga_jual', 'harga_ecer')
                ->orderBy('tanggal', 'desc')
                ->get();

            $totalStok = 0;
            return response()->json($stocks->map(function ($stock) use (&$totalStok) {
                $totalStok += $stock->stok;
                return [
                    'tanggal' => $stock->tanggal,
                    'nomor_nota' => $stock->nomor_nota,
                    'stok' => $stock->stok,
                    'total_stok' => $totalStok,
                    'harga_beli' => $stock->harga_beli,
                    'harga_jual' => $stock->harga_jual,
                    'harga_ecer' => $stock->harga_ecer
                ];
            }));
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memuat data stok'], 500);
        }
    }
}