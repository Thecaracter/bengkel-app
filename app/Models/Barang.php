<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'barcode',
        'nama',
        'kategori_id',
        'satuan_id',
        'stok',
        'stok_minimal',
        'bisa_ecer'
    ];

    protected $casts = [
        'bisa_ecer' => 'boolean',
        'stok' => 'decimal:2',
        'stok_minimal' => 'decimal:2'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class);
    }
}