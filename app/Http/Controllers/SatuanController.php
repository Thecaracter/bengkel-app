<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class SatuanController extends Controller
{
    public function index(): View
    {
        $satuans = Satuan::latest()->get();
        return view('pages.satuan', compact('satuans'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'konversi' => 'required|numeric|min:0'
            ]);

            $satuan = Satuan::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil ditambahkan',
                'data' => $satuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan satuan'
            ], 500);
        }
    }

    public function update(Request $request, Satuan $satuan): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'konversi' => 'required|numeric|min:0'
            ]);

            $satuan->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil diperbarui',
                'data' => $satuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui satuan'
            ], 500);
        }
    }

    public function destroy(Satuan $satuan): JsonResponse
    {
        try {
            $satuan->delete();
            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus satuan'
            ], 500);
        }
    }
}