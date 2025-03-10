<?php

namespace Database\Seeders;

use App\Models\BarangMasuk;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BarangMasukSeeder extends Seeder
{
    public function run()
    {
        $barangMasuks = [
            [
                'nomor_nota' => 'BM-1',
                'tanggal' => now(),
                'barang_id' => 1,
                'harga_beli' => 45000,
                'harga_jual' => 55000,
                'harga_ecer' => 30000,
                'jumlah' => 10,
                'stok' => 10
            ],
            [
                'nomor_nota' => 'BM-2',
                'tanggal' => now(),
                'barang_id' => 2,
                'harga_beli' => 35000,
                'harga_jual' => 50000,
                'jumlah' => 8,
                'stok' => 8
            ]
        ];

        foreach ($barangMasuks as $barangMasuk) {
            BarangMasuk::create($barangMasuk);
        }
    }
}