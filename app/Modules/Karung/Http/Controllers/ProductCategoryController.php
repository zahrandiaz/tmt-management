<?php

namespace App\Modules\Karung\Http\Controllers; // <--- NAMESPACE DIPERBAIKI

use App\Modules\Karung\Models\ProductCategory; // <--- USE STATEMENT MODEL DIPERBAIKI
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- TAMBAHKAN INI untuk validasi unique

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductCategory::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }
        $categories = $query->latest()->paginate(10);
        return view('karung::product_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Nanti kita isi ini untuk menampilkan form tambah kategori
        // Hanya menampilkan view yang berisi form tambah kategori
        return view('karung::product_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Nanti kita isi ini untuk menyimpan kategori baru
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_product_categories', 'name') // Nama harus unik dalam tabel ini
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    }),
                // Contoh di atas memastikan 'name' unik untuk kombinasi dengan 'business_unit_id'
            ],
            // Jika ada field lain, tambahkan validasinya di sini
        ]);

        // Tambahkan business_unit_id ke data yang akan disimpan
        $dataToStore = array_merge($validatedData, ['business_unit_id' => $currentBusinessUnitId]);

        ProductCategory::create($dataToStore);

        return redirect()->route('karung.product-categories.index')
                         ->with('success', 'Kategori produk baru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory) // Type-hint akan menggunakan model yang benar
    {
        // Nanti kita isi ini untuk menampilkan detail satu kategori
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory) // Type-hint akan menggunakan model yang benar
    {
        // Nanti kita isi ini untuk menampilkan form edit kategori
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $productCategory ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.
        // Untuk sekarang, kita asumsikan pengguna berhak mengedit.

    return view('karung::product_categories.edit', compact('productCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory) // Type-hint akan menggunakan model yang benar
    {
        // Nanti kita isi ini untuk memperbarui kategori
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        // dan pastikan $productCategory ini memang milik business_unit_id tersebut sebelum diupdate.
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        // Validasi data
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_product_categories', 'name')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($productCategory->id), // Abaikan ID kategori saat ini saat cek unique
            ],
            // Jika ada field lain, tambahkan validasinya di sini
        ]);

        // Lakukan pengecekan apakah pengguna berhak mengubah kategori ini
        // berdasarkan $currentBusinessUnitId. Untuk V1, kita bisa asumsikan ini lolos
        // jika $productCategory->business_unit_id == $currentBusinessUnitId (setelah $currentBusinessUnitId dinamis)
        // Jika tidak, return error atau redirect.
        // Contoh sederhana (akan disempurnakan nanti):
        if ($productCategory->business_unit_id != $currentBusinessUnitId && $currentBusinessUnitId != null /*atau cara lain cek super admin*/) {
             // Jika Anda ingin mengimplementasikan multi-tenancy ketat dari awal
             // return redirect()->route('karung.product-categories.index')->with('error', 'Anda tidak berhak mengubah kategori ini.');
        }


        $productCategory->update($validatedData);

        return redirect()->route('karung.product-categories.index')
                         ->with('success', 'Kategori produk berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory) // Type-hint akan menggunakan model yang benar
    {
        // Nanti kita isi ini untuk menghapus kategori
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $productCategory ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.
        // Dan juga, pertimbangkan apa yang terjadi jika kategori ini sudah digunakan oleh produk.
        // Untuk V1 CRUD dasar, kita langsung hapus. Di aplikasi nyata, mungkin perlu
        // validasi tambahan atau menggunakan soft deletes.
        $currentBusinessUnitId = 1; // Placeholder, ini harus dinamis

        // Contoh pengecekan sederhana (akan disempurnakan nanti dengan sistem hak akses TMT Core)
        if ($productCategory->business_unit_id != $currentBusinessUnitId && $currentBusinessUnitId != null /*atau cara lain cek super admin*/) {
            // Jika Anda ingin mengimplementasikan multi-tenancy ketat dari awal
            // return redirect()->route('karung.product-categories.index')->with('error', 'Anda tidak berhak menghapus kategori ini.');
        }

        try {
            $categoryName = $productCategory->name; // Simpan nama untuk pesan sukses
            $productCategory->delete();

            return redirect()->route('karung.product-categories.index')
                             ->with('success', "Kategori produk '{$categoryName}' berhasil dihapus!");
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error jika ada foreign key constraint (misalnya jika kategori sudah dipakai produk)
            // Ini jika kita TIDAK menggunakan onDelete('cascade') pada foreign key di tabel produk.
            // Untuk saat ini, kita belum punya tabel produk, jadi ini mungkin belum relevan.
            return redirect()->route('karung.product-categories.index')
                             ->with('error', "Gagal menghapus kategori '{$productCategory->name}'. Mungkin kategori ini masih digunakan oleh data lain.");
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('karung.product-categories.index')
                             ->with('error', "Terjadi kesalahan saat mencoba menghapus kategori '{$productCategory->name}'.");
        }
    }
}