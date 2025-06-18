<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Hash; // <-- Tambahkan ini
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
            'karung.edit_purchases',
            'karung.delete_purchases',

            'karung.view_sales',
            'karung.create_sales',
            'karung.cancel_sales',
            'karung.edit_sales',
            'karung.delete_sales',

            // [BARU] Permission untuk update pembayaran
            'karung.manage_payments',

            'karung.view_reports',
            'karung.manage_expenses',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }
        $this->command->info('Permissions telah dibuat/diverifikasi.');

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin TMT', 'guard_name' => 'web']);
        $adminKarungRole = Role::firstOrCreate(['name' => 'Admin Modul Karung', 'guard_name' => 'web']);
        $staffKarungRole = Role::firstOrCreate(['name' => 'Staff Modul Karung', 'guard_name' => 'web']);
        $this->command->info('Roles telah dibuat/diverifikasi.');

        // Berikan semua permissions ke Super Admin TMT
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
            'karung.cancel_purchases',
            'karung.view_sales',
            'karung.create_sales',
            'karung.cancel_sales',
            'karung.view_reports',
            'karung.manage_payments', // [BARU] Berikan akses ke Admin Modul
            'karung.manage_expenses',
        ]);
        $this->command->info("Permissions untuk 'Admin Modul Karung' telah ditetapkan.");

        $staffKarungRole->syncPermissions([
            'view tmt dashboard',
            'karung.access_module',
            'karung.view_sales',
            'karung.create_sales',
            // Secara default, staff belum bisa update pembayaran.
            // Anda bisa tambahkan 'karung.manage_payments' di sini atau via UI jika diperlukan.
        ]);
        $this->command->info("Permissions untuk 'Staff Modul Karung' telah ditetapkan.");

        // [BLOK BARU] Buat pengguna Super Admin default jika belum ada
        $superAdminUser = User::firstOrCreate(
            ['email' => 'zahrandiaz99@gmail.com'], // Kunci untuk mencari pengguna
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'), // Ganti 'password' dengan password yang aman jika perlu
            ]
        );
        // Berikan peran Super Admin TMT ke pengguna tersebut
        $superAdminUser->assignRole($superAdminRole);
        
        $this->command->info("Pengguna Super Admin default telah dibuat/diverifikasi.");
    }
}