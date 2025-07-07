<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Models\ProductType;
use App\Http\Controllers\ModuleBaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Untuk validasi unique yang lebih advance

class ProductTypeController extends ModuleBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductType::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }
        $types = $query->latest()->paginate(10);
        return view('karung::product_types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        // Hanya menampilkan view yang berisi form tambah jenis produk
        return view('karung::product_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_product_types', 'name') // Nama harus unik dalam tabel karung_product_types
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    }),
            ],
            // Jika ada field lain untuk Jenis Produk, tambahkan validasinya di sini
        ]);

        // Tambahkan business_unit_id ke data yang akan disimpan
        $dataToStore = array_merge($validatedData, ['business_unit_id' => $currentBusinessUnitId]);

        ProductType::create($dataToStore);

        return redirect()->route('karung.product-types.index')
                         ->with('success', 'Jenis produk baru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductType $productType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductType $productType)
    {
        //
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $productType ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.

        return view('karung::product_types.edit', compact('productType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductType $productType)
    {
        //
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        // dan pastikan $productType ini memang milik business_unit_id tersebut sebelum diupdate.
        $currentBusinessUnitId = 1; // Ganti dengan ID instansi bisnis yang sesuai untuk tes

        // Validasi data
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_product_types', 'name') // Cek unik di tabel karung_product_types
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($productType->id), // Abaikan ID jenis produk saat ini saat cek unique
            ],
            // Jika ada field lain, tambahkan validasinya di sini
        ]);

        // TODO: Tambahkan pengecekan otorisasi lebih lanjut di sini jika perlu,
        // misalnya memastikan pengguna hanya bisa mengedit jenis produk
        // yang sesuai dengan business_unit_id mereka.
        // if ($productType->business_unit_id != $currentBusinessUnitId && !auth()->user()->hasRole('Super Admin TMT')) {
        //     return redirect()->route('karung.product-types.index')->with('error', 'Anda tidak berhak mengubah jenis produk ini.');
        // }


        $productType->update($validatedData);

        return redirect()->route('karung.product-types.index')
                         ->with('success', 'Jenis produk berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductType $productType)
    {
        //
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $productType ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.
        // Dan juga, pertimbangkan apa yang terjadi jika jenis ini sudah digunakan oleh produk.
        // Untuk V1 CRUD dasar, kita langsung hapus. Di aplikasi nyata, mungkin perlu
        // validasi tambahan atau menggunakan soft deletes.
        $currentBusinessUnitId = 1; // Placeholder, ini harus dinamis

        // Contoh pengecekan sederhana (akan disempurnakan nanti)
        if ($productType->business_unit_id != $currentBusinessUnitId && $currentBusinessUnitId != null /*atau cara lain cek super admin*/) {
             // return redirect()->route('karung.product-types.index')->with('error', 'Anda tidak berhak menghapus jenis produk ini.');
        }

        try {
            $typeName = $productType->name; // Simpan nama untuk pesan sukses
            $productType->delete();

            return redirect()->route('karung.product-types.index')
                             ->with('success', "Jenis produk '{$typeName}' berhasil dihapus!");
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error jika ada foreign key constraint (misalnya jika jenis sudah dipakai produk)
            return redirect()->route('karung.product-types.index')
                             ->with('error', "Gagal menghapus jenis produk '{$productType->name}'. Mungkin jenis ini masih digunakan oleh data lain.");
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('karung.product-types.index')
                             ->with('error', "Terjadi kesalahan saat mencoba menghapus jenis produk '{$productType->name}'.");
        }
    }
}
