<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BarangSeeder extends Seeder
{
    public function run()
    {
        $barangs = [
            [
                'barcode' => '8997777123456',
                'nama' => 'Oli Mesin XYZ 4T 1L',
                'kategori_id' => 1, // Oli
                'satuan_id' => 4, // Botol
                'stok' => 10,
                'stok_minimal' => 5,
                'bisa_ecer' => true
            ],
            [
                'barcode' => '8995671234567',
                'nama' => 'Filter Udara Yamaha NMAX',
                'kategori_id' => 7, // Filter
                'satuan_id' => 1, // Pcs
                'stok' => 8,
                'stok_minimal' => 3,
                'bisa_ecer' => false
            ],
            [
                'barcode' => '8994561234567',
                'nama' => 'Kampas Rem Depan Honda Beat',
                'kategori_id' => 12, // Kampas Rem
                'satuan_id' => 13, // Pack
                'stok' => 6,
                'stok_minimal' => 2,
                'bisa_ecer' => false
            ],
        ];

        foreach ($barangs as $barang) {
            Barang::create($barang);
        }
    }
}
