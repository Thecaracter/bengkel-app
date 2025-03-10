<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class PengeluaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $search = $request->get('search', '');
            $filter_type = $request->get('filter_type', '');
            $filter_value = $request->get('filter_value', '');
            $start_date = $request->get('start_date', '');
            $end_date = $request->get('end_date', '');

            $query = Pengeluaran::query();

            // Filter berdasarkan search
            if (!empty($search)) {
                $query->where('nama_pengeluaran', 'like', "%{$search}%")
                    ->orWhere('tanggal', 'like', "%{$search}%");
            }

            // Filter berdasarkan tipe
            if (!empty($filter_type) && !empty($filter_value)) {
                if ($filter_type === 'month') {
                    // Bulan format: YYYY-MM
                    $year = substr($filter_value, 0, 4);
                    $month = substr($filter_value, 5, 2);
                    $query->whereYear('tanggal', $year)
                        ->whereMonth('tanggal', $month);
                } elseif ($filter_type === 'year') {
                    // Tahun format: YYYY
                    $query->whereYear('tanggal', $filter_value);
                }
            }

            // Filter berdasarkan custom date range
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('tanggal', [$start_date, $end_date]);
            }

            $pengeluarans = $query->orderBy('tanggal', 'desc')->get();

            // Menghitung total pengeluaran
            $total = $pengeluarans->sum('jumlah');

            return response()->json([
                'data' => $pengeluarans,
                'total' => $total
            ]);
        }

        return view('pages.pengeluaran');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pengeluaran' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $pengeluaran = Pengeluaran::create($request->all());

        return response()->json([
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $pengeluaran,
            'id' => $pengeluaran->id
        ]);
    }
    /**
     * Display the specified resource.
     *
     * @param Pengeluaran $pengeluaran
     * @return JsonResponse
     */
    public function show(Pengeluaran $pengeluaran)
    {
        return response()->json($pengeluaran);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Pengeluaran $pengeluaran
     * @return JsonResponse
     */
    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        $validator = Validator::make($request->all(), [
            'nama_pengeluaran' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $pengeluaran->update($request->all());

        return response()->json([
            'message' => 'Pengeluaran berhasil diperbarui',
            'data' => $pengeluaran
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Pengeluaran $pengeluaran
     * @return JsonResponse
     */
    public function destroy(Pengeluaran $pengeluaran)
    {
        try {
            $pengeluaran->delete();

            return response()->json([
                'message' => 'Pengeluaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }
}