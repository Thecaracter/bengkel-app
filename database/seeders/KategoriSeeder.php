<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $kategoris = [
            ['nama' => 'Oli'],
            ['nama' => 'Spare Part Mesin'],
            ['nama' => 'Body Part'],
            ['nama' => 'Ban & Velg'],
            ['nama' => 'Aksesoris'],
            ['nama' => 'Minyak Rem'],
            ['nama' => 'Filter'],
            ['nama' => 'Bearing'],
            ['nama' => 'Seal'],
            ['nama' => 'Busi'],
            ['nama' => 'Lampu'],
            ['nama' => 'Kampas Rem'],
            ['nama' => 'Kampas Kopling'],
            ['nama' => 'Kabel'],
            ['nama' => 'Tools'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::create($kategori);
        }
    }
}
