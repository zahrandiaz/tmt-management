<?php

namespace App\Modules\Karung\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Karung\Models\Setting;

class KarungSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ID Instansi Bisnis default (hardcoded untuk sekarang)
        $defaultBusinessUnitId = 1;

        // Pengaturan untuk Stok Otomatis
        Setting::firstOrCreate(
            [
                'business_unit_id' => $defaultBusinessUnitId,
                'setting_key'      => 'automatic_stock_management',
            ],
            [
                'setting_value'    => 'false', // Nilai default: tidak aktif
            ]
        );

        // Anda bisa menambahkan pengaturan lain untuk modul Karung di sini di masa depan
        // Contoh:
        // Setting::firstOrCreate(
        //     [
        //         'business_unit_id' => $defaultBusinessUnitId,
        //         'setting_key'      => 'default_tax_rate',
        //     ],
        //     [
        //         'setting_value'    => '11',
        //     ]
        // );

        $this->command->info('Default settings for Karung module have been seeded.');
    }
}
