<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    protected $table = 'satuan';

    protected $fillable = [
        'nama',
        'konversi'
    ];

    protected $casts = [
        'konversi' => 'decimal:2'
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class);
    }
}