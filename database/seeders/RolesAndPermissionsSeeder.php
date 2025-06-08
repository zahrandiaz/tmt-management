<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Pastikan model User Anda di-import
use Spatie\Permission\PermissionRegistrar; // Import PermissionRegistrar

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
            // Permissions untuk TMT Management (Aplikasi Inti)
            'manage tmt settings',
            'manage users',
            'view tmt dashboard',

            // Permissions untuk Modul Toko Karung
            'karung.access_module',
            'karung.manage_products',
            'karung.create_sales',
            'karung.view_reports',
            // Tambahkan permissions lain untuk modul Karung jika ada
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
        $superAdminRole->syncPermissions(Permission::all()); // syncPermissions lebih aman
        $this->command->info("Semua permissions telah diberikan ke 'Super Admin TMT'.");

        // Berikan permissions ke Admin Modul Karung
        $adminKarungRole->syncPermissions([
            'view tmt dashboard',
            'karung.access_module',
            'karung.manage_products',
            'karung.create_sales',
            'karung.view_reports',
        ]);
        $this->command->info("Permissions untuk 'Admin Modul Karung' telah ditetapkan.");

        // Berikan permissions ke Staff Modul Karung
        $staffKarungRole->syncPermissions([
            'view tmt dashboard',
            'karung.access_module',
            'karung.create_sales',
        ]);
        $this->command->info("Permissions untuk 'Staff Modul Karung' telah ditetapkan.");

        // --- CARI DAN TETAPKAN PERAN UNTUK PENGGUNA ANDA ---
        // !!! GANTI 'email_anda_yang_terdaftar@example.com' DENGAN ALAMAT EMAIL YANG ANDA GUNAKAN SAAT REGISTRASI !!!
        $superAdminUserEmail = 'zahrandiaz99@gmail.com'; // <--- GANTI BAGIAN INI
        // --- -------------------------------------------- ---

        $superAdminUser = User::where('email', $superAdminUserEmail)->first();

        if ($superAdminUser) {
            if (!$superAdminUser->hasRole('Super Admin TMT')) {
                $superAdminUser->assignRole($superAdminRole);
                $this->command->info("Peran 'Super Admin TMT' berhasil ditetapkan ke pengguna: " . $superAdminUserEmail);
            } else {
                $this->command->info("Pengguna " . $superAdminUserEmail . " sudah memiliki peran 'Super Admin TMT'.");
            }
        } else {
            $this->command->warn("PENGGUNA DENGAN EMAIL '" . $superAdminUserEmail . "' TIDAK DITEMUKAN.");
            $this->command->warn("Peran 'Super Admin TMT' belum ditetapkan ke pengguna manapun.");
            $this->command->warn("Pastikan Anda sudah mengganti 'email_anda_yang_terdaftar@example.com' di file seeder dengan email yang benar, lalu jalankan seeder ini lagi.");
        }
        $this->command->info('Proses seeding Roles dan Permissions selesai.');
    }
}