<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password')
        ]);
        User::factory()->create([
            'name' => 'user',
            'email' => 'penjualan@gmail.com',
            'password' => Hash::make('password')
        ]);
        $this->call([
            SatuanSeeder::class,
            KategoriSeeder::class,
            // BarangSeeder::class,
            // BarangMasukSeeder::class,
        ]);
    }
}
