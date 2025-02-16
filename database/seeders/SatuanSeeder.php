<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $satuans = [
            ['nama' => 'Pcs', 'konversi' => 1],
            ['nama' => 'Box', 'konversi' => 1],
            ['nama' => 'Set', 'konversi' => 1],
            ['nama' => 'Botol', 'konversi' => 1],
            ['nama' => 'Setengah Botol', 'konversi' => 0.5],
            ['nama' => 'Seperempat Botol', 'konversi' => 0.25],
            ['nama' => 'Liter', 'konversi' => 1],
            ['nama' => 'Setengah Liter', 'konversi' => 0.5],
            ['nama' => 'Seperempat Liter', 'konversi' => 0.25],
            ['nama' => 'Kaleng', 'konversi' => 1],
            ['nama' => 'Roll', 'konversi' => 1],
            ['nama' => 'Meter', 'konversi' => 1],
            ['nama' => 'Pack', 'konversi' => 1],
            ['nama' => 'Pasang', 'konversi' => 1],
        ];

        foreach ($satuans as $satuan) {
            Satuan::create($satuan);
        }
    }
}
