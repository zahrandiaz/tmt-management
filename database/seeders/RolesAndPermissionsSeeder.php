<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // TMT Core
            'manage tmt settings',
            'manage users',
            'view tmt dashboard',
            'view system logs',

            // Modul Toko Karung
            'karung.access_module',
            'karung.manage_products',
            'karung.manage_categories',
            'karung.manage_types',
            'karung.manage_suppliers',
            'karung.manage_customers',

            'karung.view_purchases',
            'karung.create_purchases',
            'karung.cancel_purchases',
            'karung.edit_purchases',   // <-- [BARU]
            'karung.delete_purchases', // <-- [BARU]

            'karung.view_sales',
            'karung.create_sales',
            'karung.cancel_sales',
            'karung.edit_sales',     // <-- [BARU]
            'karung.delete_sales',   // <-- [BARU]

            'karung.view_reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }
        $this->command->info('Permissions telah dibuat/diverifikasi.');

        // ... (sisa kode tidak berubah) ...
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin TMT', 'guard_name' => 'web']);
        $adminKarungRole = Role::firstOrCreate(['name' => 'Admin Modul Karung', 'guard_name' => 'web']);
        $staffKarungRole = Role::firstOrCreate(['name' => 'Staff Modul Karung', 'guard_name' => 'web']);
        $this->command->info('Roles telah dibuat/diverifikasi.');

        // Berikan semua permissions ke Super Admin TMT (otomatis termasuk yang baru)
        $superAdminRole->syncPermissions(Permission::all());
        $this->command->info("Semua permissions telah diberikan ke 'Super Admin TMT'.");

        // Pastikan peran lain tidak memiliki akses edit/delete
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
            'karung.cancel_purchases', // <-- Admin hanya bisa cancel
            'karung.view_sales',
            'karung.create_sales',
            'karung.cancel_sales',   // <-- Admin hanya bisa cancel
            'karung.view_reports',
        ]);
        $this->command->info("Permissions untuk 'Admin Modul Karung' telah ditetapkan.");

        $staffKarungRole->syncPermissions([
            'view tmt dashboard',
            'karung.access_module',
            'karung.view_sales',
            'karung.create_sales',
        ]);
        $this->command->info("Permissions untuk 'Staff Modul Karung' telah ditetapkan.");
    }
}