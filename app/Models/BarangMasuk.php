<?php

namespace App\Models;

use App\Models\BarangKeluar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';

    protected $fillable = [
        'nomor_nota',
        'tanggal',
        'harga_beli',
        'harga_jual',
        'harga_ecer',
        'jumlah',
        'stok',
        'barang_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'harga_ecer' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'stok' => 'decimal:2'
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function barangKeluarDetails(): HasMany
    {
        return $this->hasMany(BarangKeluarDetail::class, 'barang_masuk_id');
    }
}