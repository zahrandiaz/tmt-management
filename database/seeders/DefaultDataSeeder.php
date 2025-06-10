<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Karung\Models\Supplier;
use App\Modules\Karung\Models\Customer;

class DefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ID Instansi Bisnis default (hardcoded untuk sekarang)
        $defaultBusinessUnitId = 1;

        // Membuat atau menemukan Supplier "Umum"
        Supplier::firstOrCreate(
            [
                'name' => 'Pembelian Umum', // Kunci untuk mencari
                'business_unit_id' => $defaultBusinessUnitId,
            ],
            [
                // Data tambahan jika record baru dibuat
                'contact_person' => 'N/A',
                'phone_number' => 'N/A',
            ]
        );

        // Membuat atau menemukan Pelanggan "Umum"
        Customer::firstOrCreate(
            [
                'name' => 'Pelanggan Umum', // Kunci untuk mencari
                'business_unit_id' => $defaultBusinessUnitId,
            ],
            [
                // Data tambahan jika record baru dibuat
                'phone_number' => 'N/A',
            ]
        );
        
        // Menampilkan pesan di konsol bahwa seeder berhasil dijalankan
        $this->command->info('Default Supplier (Pembelian Umum) and Customer (Pelanggan Umum) have been created or verified.');
    }
}
