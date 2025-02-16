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
    ];

    public function detail()
    {
        return $this->hasMany(BarangKeluarDetail::class, 'barang_keluar_id');
    }
}