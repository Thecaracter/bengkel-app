<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table = 'barang_keluar';
    protected $dates = ['tanggal'];

    protected $fillable = [
        'tanggal',
        'keterangan',
        'total_harga',
        'jumlah_bayar',
        'kembalian'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_harga' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2'
    ];

    public function detail()
    {
        return $this->hasMany(BarangKeluarDetail::class, 'barang_keluar_id');
    }
}