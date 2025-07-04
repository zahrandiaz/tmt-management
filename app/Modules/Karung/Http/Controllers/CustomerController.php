<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Modules\Karung\Models\Customer; // Pastikan ini sudah benar
use App\Http\Controllers\ModuleBaseController;
use Illuminate\Http\Request; // Mungkin akan kita gunakan nanti
use Illuminate\Validation\Rule; // Untuk validasi unique yang lebih advance

class CustomerController extends ModuleBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('customer_code', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        $customers = $query->latest()->paginate(10);
        return view('karung::customers.index', compact('customers'));
    }

    public function create()
    {
        // Hanya menampilkan view yang berisi form tambah pelanggan
        return view('karung::customers.create');
    }

    public function store(Request $request)
    {
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'customer_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('karung_customers', 'customer_code')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore(null, 'id')
                    ->whereNotNull('customer_code'),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('karung_customers', 'email')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore(null, 'id')
                    ->whereNotNull('email'),
            ],
            'address' => ['nullable', 'string'],
        ]);

        // Tambahkan business_unit_id ke data yang akan disimpan
        $dataToStore = array_merge($validatedData, ['business_unit_id' => $currentBusinessUnitId]);

        Customer::create($dataToStore);

        return redirect()->route('karung.customers.index')
                         ->with('success', 'Pelanggan baru berhasil ditambahkan!');
    }

    /**
     * [BARU] Menampilkan riwayat transaksi untuk satu pelanggan.
     */
    public function history(Customer $customer)
    {
        // Ambil transaksi penjualan milik pelanggan ini
        // Pastikan hanya yang berstatus 'Completed'
        $sales = $customer->salesTransactions()
                          ->where('status', 'Completed')
                          ->latest()
                          ->paginate(15);

        // Kirim data pelanggan dan transaksinya ke view baru
        return view('karung::customers.history', compact('customer', 'sales'));
    }

    public function edit(Customer $customer) // Laravel otomatis mengambil data pelanggan
    {
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $customer ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.

        return view('karung::customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        // dan pastikan $customer ini memang milik business_unit_id tersebut sebelum diupdate.
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        // Validasi data
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'customer_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('karung_customers', 'customer_code')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($customer->id) // Abaikan ID pelanggan saat ini
                    ->whereNotNull('customer_code'),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('karung_customers', 'email')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($customer->id) // Abaikan ID pelanggan saat ini
                    ->whereNotNull('email'),
            ],
            'address' => ['nullable', 'string'],
        ]);

        // TODO: Tambahkan pengecekan otorisasi lebih lanjut di sini jika perlu.
        // if ($customer->business_unit_id != $currentBusinessUnitId && !auth()->user()->hasRole('Super Admin TMT')) {
        //     return redirect()->route('karung.customers.index')->with('error', 'Anda tidak berhak mengubah pelanggan ini.');
        // }

        $customer->update($validatedData);

        return redirect()->route('karung.customers.index')
                         ->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    public function destroy(Customer $customer)
    {
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $customer ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.
        // Dan juga, pertimbangkan apa yang terjadi jika pelanggan ini sudah memiliki transaksi penjualan.
        // Untuk V1 CRUD dasar, kita langsung hapus. Di aplikasi nyata, mungkin perlu
        // validasi tambahan, menggunakan soft deletes, atau menonaktifkan pelanggan.
        $currentBusinessUnitId = 1; // Placeholder, ini harus dinamis

        // Contoh pengecekan sederhana (akan disempurnakan nanti)
        // if ($customer->business_unit_id != $currentBusinessUnitId && !auth()->user()->hasRole('Super Admin TMT')) {
        //     return redirect()->route('karung.customers.index')->with('error', 'Anda tidak berhak menghapus pelanggan ini.');
        // }

        try {
            $customerName = $customer->name; // Simpan nama untuk pesan sukses
            $customer->delete();

            return redirect()->route('karung.customers.index')
                             ->with('success', "Data pelanggan '{$customerName}' berhasil dihapus!");
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error jika ada foreign key constraint (misalnya jika pelanggan sudah dipakai di tabel penjualan)
            // Ini akan relevan jika kita sudah punya tabel penjualan dan relasi.
            return redirect()->route('karung.customers.index')
                             ->with('error', "Gagal menghapus pelanggan '{$customer->name}'. Mungkin pelanggan ini masih terhubung dengan data transaksi penjualan.");
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('karung.customers.index')
                             ->with('error', "Terjadi kesalahan saat mencoba menghapus pelanggan '{$customer->name}'.");
        }
    }
    // ... (method CRUD lainnya: show) ...
}