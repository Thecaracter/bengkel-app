<?php

namespace App\Models;

use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangKeluarDetail extends Model
{
    use HasFactory;

    protected $table = 'barang_keluar_detail';

    protected $fillable = [
        'barang_keluar_id',
        'barang_masuk_id',
        'jumlah',
        'harga',
        'tipe',
    ];

    // Relasi ke BarangKeluar
    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluar::class, 'barang_keluar_id');
    }

    // Relasi ke BarangMasuk
    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'barang_masuk_id');
    }
}