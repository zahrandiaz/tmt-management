<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Modules\Karung\Models\Supplier; // Pastikan ini sudah benar
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // Mungkin akan kita gunakan nanti
use Illuminate\Validation\Rule; // Untuk validasi unique yang lebih advance

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Tambahkan Request $request
    {
        // TODO: Filter berdasarkan business_unit_id yang aktif.
        // $currentBusinessUnitId = 1;

        // Mulai query
        $query = Supplier::query();

        // Cek apakah ada input pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            // Tambahkan kondisi where untuk memfilter berdasarkan nama ATAU kode supplier
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('supplier_code', 'like', '%' . $searchTerm . '%');
            });
        }

        // Lanjutkan query dengan urutan dan paginasi
        $suppliers = $query->latest()->paginate(10);

        // Mengirim data $suppliers ke view
        return view('karung::suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        // Hanya menampilkan view yang berisi form tambah supplier
        return view('karung::suppliers.create');
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
                Rule::unique('karung_suppliers', 'name')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    }),
            ],
            'supplier_code' => [
                'nullable',
                'string',
                'max:50',
                // Kode supplier harus unik per business_unit_id jika diisi
                Rule::unique('karung_suppliers', 'supplier_code')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore(null, 'id') // Ini penting agar validasi unique bekerja benar untuk field nullable
                    ->whereNotNull('supplier_code'), // Hanya berlaku jika supplier_code tidak null
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                // Email harus unik per business_unit_id jika diisi
                Rule::unique('karung_suppliers', 'email')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore(null, 'id') // Ini penting agar validasi unique bekerja benar untuk field nullable
                    ->whereNotNull('email'), // Hanya berlaku jika email tidak null
            ],
            'address' => ['nullable', 'string'],
        ]);

        // Tambahkan business_unit_id ke data yang akan disimpan
        $dataToStore = array_merge($validatedData, ['business_unit_id' => $currentBusinessUnitId]);

        Supplier::create($dataToStore);

        return redirect()->route('karung.suppliers.index')
                         ->with('success', 'Supplier baru berhasil ditambahkan!');
    }

    public function edit(Supplier $supplier) // Laravel otomatis mengambil data supplier
    {
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $supplier ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.

        return view('karung::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        // dan pastikan $supplier ini memang milik business_unit_id tersebut sebelum diupdate.
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        // Validasi data
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_suppliers', 'name')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($supplier->id), // Abaikan ID supplier saat ini
            ],
            'supplier_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('karung_suppliers', 'supplier_code')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($supplier->id) // Abaikan ID supplier saat ini
                    ->whereNotNull('supplier_code'),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('karung_suppliers', 'email')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($supplier->id) // Abaikan ID supplier saat ini
                    ->whereNotNull('email'),
            ],
            'address' => ['nullable', 'string'],
        ]);

        // TODO: Tambahkan pengecekan otorisasi lebih lanjut di sini jika perlu,
        // misalnya memastikan pengguna hanya bisa mengedit supplier
        // yang sesuai dengan business_unit_id mereka.
        // if ($supplier->business_unit_id != $currentBusinessUnitId && !auth()->user()->hasRole('Super Admin TMT')) {
        //     return redirect()->route('karung.suppliers.index')->with('error', 'Anda tidak berhak mengubah supplier ini.');
        // }

        $supplier->update($validatedData);

        return redirect()->route('karung.suppliers.index')
                         ->with('success', 'Data supplier berhasil diperbarui!');
    }

    public function destroy(Supplier $supplier)
    {
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $supplier ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.
        // Dan juga, pertimbangkan apa yang terjadi jika supplier ini sudah digunakan oleh data pembelian.
        // Untuk V1 CRUD dasar, kita langsung hapus. Di aplikasi nyata, mungkin perlu
        // validasi tambahan, menggunakan soft deletes, atau menonaktifkan supplier.
        $currentBusinessUnitId = 1; // Placeholder, ini harus dinamis

        // Contoh pengecekan sederhana (akan disempurnakan nanti)
        // if ($supplier->business_unit_id != $currentBusinessUnitId && !auth()->user()->hasRole('Super Admin TMT')) {
        //     return redirect()->route('karung.suppliers.index')->with('error', 'Anda tidak berhak menghapus supplier ini.');
        // }

        try {
            $supplierName = $supplier->name; // Simpan nama untuk pesan sukses
            $supplier->delete();

            return redirect()->route('karung.suppliers.index')
                             ->with('success', "Data supplier '{$supplierName}' berhasil dihapus!");
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error jika ada foreign key constraint (misalnya jika supplier sudah dipakai di tabel pembelian)
            // Ini akan relevan jika kita sudah punya tabel pembelian dan relasi.
            return redirect()->route('karung.suppliers.index')
                             ->with('error', "Gagal menghapus supplier '{$supplier->name}'. Mungkin supplier ini masih terhubung dengan data transaksi pembelian.");
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('karung.suppliers.index')
                             ->with('error', "Terjadi kesalahan saat mencoba menghapus supplier '{$supplier->name}'.");
        }
    }
    // ... (method CRUD lainnya: show) ...
}