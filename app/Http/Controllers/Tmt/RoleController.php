<?php

namespace App\Http\Controllers\Tmt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role; // Pastikan ini ada
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua peran, urutkan dari yang terbaru, dan gunakan paginasi
        // withCount('users') akan secara efisien menghitung jumlah pengguna untuk setiap peran
        $roles = Role::withCount('users')->latest()->paginate(15);

        return view('tmt.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua permissions yang ada untuk ditampilkan sebagai pilihan (checkbox)
        $permissions = Permission::all();

        return view('tmt.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'], // Pastikan setiap permission yang dikirim ada di database
        ]);

        try {
            // Memulai Database Transaction
            DB::beginTransaction();

            // 2. Buat Peran (Role) Baru
            $role = Role::create(['name' => $request->name]);

            // 3. Tetapkan Hak Akses (Permissions) ke Peran tersebut
            // syncPermissions akan memastikan hanya permission yang dipilih yang terhubung
            $role->syncPermissions($request->input('permissions'));

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('tmt.admin.roles.index')
                            ->with('success', 'Peran baru berhasil dibuat.');

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query yang sudah dijalankan
            DB::rollBack();

            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat membuat peran baru: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Pengaman: Jangan biarkan peran Super Admin TMT diedit melalui UI ini
        // untuk mencegah terhapusnya semua hak akses secara tidak sengaja.
        if ($role->name == 'Super Admin TMT') {
            return redirect()->route('tmt.admin.roles.index')
                            ->with('error', 'Peran Super Admin tidak dapat diubah.');
        }

        // Ambil semua permissions yang ada untuk ditampilkan sebagai pilihan (checkbox)
        $permissions = Permission::all();

        // Ambil permissions yang sudah dimiliki oleh peran ini untuk pre-check checkbox
        $rolePermissions = $role->permissions->pluck('name')->all();

        return view('tmt.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Pengaman tambahan: pastikan lagi peran Super Admin tidak diubah.
        if ($role->name == 'Super Admin TMT') {
            return redirect()->route('tmt.admin.roles.index')
                            ->with('error', 'Peran Super Admin tidak dapat diubah.');
        }

        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        try {
            // Memulai Database Transaction
            DB::beginTransaction();

            // 2. Update nama peran
            $role->update(['name' => $request->name]);

            // 3. Sinkronkan Hak Akses (Permissions)
            $role->syncPermissions($request->input('permissions'));

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('tmt.admin.roles.index')
                            ->with('success', 'Peran berhasil diperbarui.');

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query yang sudah dijalankan
            DB::rollBack();

            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat memperbarui peran: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Pengaman 1: Jangan biarkan peran Super Admin TMT dihapus.
        if ($role->name == 'Super Admin TMT') {
            return redirect()->route('tmt.admin.roles.index')
                            ->with('error', 'Peran Super Admin tidak dapat dihapus.');
        }

        // Pengaman 2: Jangan hapus peran jika masih ada pengguna yang memilikinya.
        if ($role->users()->count() > 0) {
            return redirect()->route('tmt.admin.roles.index')
                            ->with('error', "Peran '{$role->name}' tidak dapat dihapus karena masih digunakan oleh {$role->users()->count()} pengguna.");
        }

        // Simpan nama peran untuk pesan sukses sebelum dihapus
        $roleName = $role->name;

        // Hapus peran. Package Spatie akan otomatis menghapus relasi di role_has_permissions.
        $role->delete();

        return redirect()->route('tmt.admin.roles.index')
                        ->with('success', "Peran '{$roleName}' berhasil dihapus.");
    }
    // ... (method CRUD lainnya) ...
}