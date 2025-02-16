<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::latest()->get();
        return view('pages.kategori', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori,nama'
        ]);

        Kategori::create($request->only('nama'));
        return response()->json(['message' => 'Kategori berhasil ditambahkan']);
    }

    public function show(Kategori $kategori)
    {
        return response()->json($kategori);
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('kategori')->ignore($kategori->id)]
        ]);

        $kategori->update($request->only('nama'));
        return response()->json(['message' => 'Kategori berhasil diperbarui']);
    }

    public function destroy(Kategori $kategori)
    {
        try {
            $kategori->delete();
            return response()->json(['message' => 'Kategori berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan'
            ], 422);
        }
    }
}