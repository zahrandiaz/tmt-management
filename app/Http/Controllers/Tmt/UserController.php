<?php

namespace App\Http\Controllers\Tmt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash; // <-- TAMBAHKAN INI
use Illuminate\Validation\Rules;      // <-- TAMBAHKAN INI JUGA
use Illuminate\Validation\Rule;      // <-- PASTIKAN BARIS INI ADA

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua pengguna, beserta relasi 'roles' mereka untuk ditampilkan
        // Eager loading 'roles' agar lebih efisien
        $users = User::with('roles')->latest()->paginate(15);

        // Mengirim data $users ke view 'tmt.users.index'
        // Kita akan buat view ini setelah ini.
        return view('tmt.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua peran yang ada untuk ditampilkan sebagai pilihan di form
        $roles = Role::all();

        return view('tmt.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array'], // Pastikan roles adalah array
            'roles.*' => ['string', 'exists:roles,name'], // Pastikan setiap item di array roles ada di tabel roles
        ]);

        // 2. Buat Pengguna Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'email_verified_at' => now(), // Opsional: jika ingin pengguna baru langsung terverifikasi
        ]);

        // 3. Tetapkan Peran (Assign Role)
        // Menggunakan metode dari Spatie untuk menetapkan peran
        $user->assignRole($request->input('roles'));

        // 4. Redirect dengan Pesan Sukses
        return redirect()->route('tmt.admin.users.index')
                        ->with('success', 'Pengguna baru berhasil dibuat.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Ambil semua peran yang ada untuk ditampilkan sebagai pilihan di form
        $roles = Role::all();

        // Mengirim data pengguna yang akan diedit dan daftar semua peran ke view
        return view('tmt.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password opsional
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        // 2. Siapkan data pengguna untuk diupdate
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // 3. Hanya update password jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // 4. Update data pengguna
        $user->update($userData);

        // 5. Sinkronkan Peran (Sync Roles)
        // Menggunakan syncRoles agar peran lama dihapus dan diganti dengan yang baru dipilih
        $user->syncRoles($request->input('roles'));

        // 6. Redirect dengan Pesan Sukses
        return redirect()->route('tmt.admin.users.index')
                        ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Pengecekan penting: Jangan biarkan pengguna menghapus dirinya sendiri.
        if (auth()->id() == $user->id) {
            return redirect()->route('tmt.admin.users.index')
                            ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Simpan nama pengguna untuk pesan sukses sebelum dihapus
        $userName = $user->name;

        // Hapus pengguna. Package Spatie akan otomatis menghapus relasi peran/izin.
        $user->delete();

        return redirect()->route('tmt.admin.users.index')
                        ->with('success', "Pengguna '{$userName}' berhasil dihapus.");
    }

    // ... (method store() dan lainnya di bawah) ...

}