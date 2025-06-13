<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar permissions yang akan dibuat
        $permissions = [
            'manage tmt settings',
            'manage users',
            'view tmt dashboard',
            'view system logs', // Permission baru kita

            // Permissions untuk Modul Toko Karung
            'karung.access_module',
            'karung.manage_products',
            'karung.manage_categories',
            'karung.manage_types',
            'karung.manage_suppliers',
            'karung.manage_customers',
            
            'karung.view_purchases',
            'karung.create_purchases',
            'karung.cancel_purchases',

            'karung.view_sales',
            'karung.create_sales',
            'karung.cancel_sales',

            'karung.view_reports',
        ];

        // Buat permissions jika belum ada
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }
        $this->command->info('Permissions telah dibuat/diverifikasi.');

        // Buat Roles jika belum ada
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin TMT', 'guard_name' => 'web']);
        $adminKarungRole = Role::firstOrCreate(['name' => 'Admin Modul Karung', 'guard_name' => 'web']);
        $staffKarungRole = Role::firstOrCreate(['name' => 'Staff Modul Karung', 'guard_name' => 'web']);
        $this->command->info('Roles telah dibuat/diverifikasi.');

        // Berikan semua permissions ke Super Admin TMT
        $superAdminRole->syncPermissions(Permission::all());
        $this->command->info("Semua permissions telah diberikan ke 'Super Admin TMT'.");

        // Berikan permissions ke Admin Modul Karung
        $adminKarungRole->syncPermissions([
            'view tmt dashboard',
            'karung.access_module',
            'karung.manage_products',
            'karung.manage_categories',
            'karung.manage_types',
            'karung.manage_suppliers',
            'karung.manage_customers',
            'karung.view_purchases',
            'karung.create_purchases',
            'karung.cancel_purchases',
            'karung.view_sales',
            'karung.create_sales',
            'karung.cancel_sales',
            'karung.view_reports',
        ]);
        $this->command->info("Permissions untuk 'Admin Modul Karung' telah ditetapkan.");

        // Berikan permissions ke Staff Modul Karung/Kasir
        $staffKarungRole->syncPermissions([
            'view tmt dashboard',
            'karung.access_module',
            'karung.view_sales', // Mungkin kasir juga butuh lihat riwayat penjualan
            'karung.create_sales',
        ]);
        // [PERBAIKAN] Mengubah tanda titik menjadi tanda panah
        $this->command->info("Permissions untuk 'Staff Modul Karung' telah ditetapkan.");
        
        // ... (sisa kode untuk assign role ke Super Admin) ...
    }
}