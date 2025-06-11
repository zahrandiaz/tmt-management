<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Karung\Database\Seeders\KarungSettingsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Memanggil seeder untuk Peran dan Hak Akses terlebih dahulu
        $this->call(RolesAndPermissionsSeeder::class);

        // Memanggil seeder baru kita untuk data default
        $this->call(DefaultDataSeeder::class);

        // Anda bisa memanggil seeder lain di sini jika ada
        
         // Memanggil seeder baru kita untuk pengaturan modul
        $this->call(KarungSettingsSeeder::class); // <-- TAMBAHKAN BARIS INI
    }
}
