<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Modules\Karung\Models\Product; // Pastikan ini sudah benar
use App\Modules\Karung\Models\ProductCategory;
use App\Modules\Karung\Models\ProductType;
use App\Modules\Karung\Models\Supplier;
use App\Http\Controllers\ModuleBaseController;
use Illuminate\Http\Request; // Mungkin akan kita gunakan nanti
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // Untuk helper string seperti Str::upper, Str::random
use Illuminate\Support\Facades\Storage; // <-- PASTIKAN INI ADA
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Gate; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\DB; // <-- TAMBAHKAN INI JUGA

class ProductController extends ModuleBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Tambahkan Request $request di sini
    {
        // PENTING: Sama seperti sebelumnya, untuk sekarang kita ambil SEMUA produk.
        // Nanti, ini HARUS difilter berdasarkan 'business_unit_id' yang aktif.
        $currentBusinessUnitId = 1; // Contoh hardcode

        // Mulai query dengan eager loading
        $query = Product::with(['category', 'type']);

        // TODO: Tambahkan filter business_unit_id di sini saat sudah dinamis
        // $query->where('business_unit_id', $currentBusinessUnitId);

        // Cek apakah ada input pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            // Tambahkan kondisi where untuk memfilter berdasarkan nama produk ATAU SKU
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        // Lanjutkan query dengan urutan dan paginasi
        $products = $query->latest()->paginate(10);

        // Mengirim data $products ke view
        return view('karung::products.index', compact('products'));
    }

    public function create()
    {
        // TODO: Nantinya, semua data ini (categories, types, suppliers)
        // HARUS difilter berdasarkan 'business_unit_id' yang aktif.
        // Untuk sekarang, kita ambil semua dulu untuk tes.
        // $currentBusinessUnitId = 1; // Contoh hardcode

        $categories = ProductCategory::orderBy('name', 'asc')->get(); // Ambil semua kategori, urutkan berdasarkan nama
        $types = ProductType::orderBy('name', 'asc')->get();       // Ambil semua jenis, urutkan berdasarkan nama
        $suppliers = Supplier::orderBy('name', 'asc')->get();     // Ambil semua supplier, urutkan berdasarkan nama

        return view('karung::products.create', compact('categories', 'types', 'suppliers'));
    }

    public function store(Request $request)
    {
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        $currentBusinessUnitId = 1;

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Nama produk sebaiknya unik per business_unit_id
                Rule::unique('karung_products', 'name')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    }),
            ],
            'product_category_id' => ['nullable', 'integer', 'exists:karung_product_categories,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:karung_product_types,id'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'default_supplier_id' => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'image_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Validasi untuk gambar
            'is_active' => ['sometimes', 'boolean'], // 'sometimes' karena checkbox jika tidak dicentang tidak mengirimkan value
        ]);

        // Buat SKU unik
        // Strategi sederhana: Prefix + BusinessUnitID + Timestamp/UniqID
        // Pastikan SKU unik secara global karena ada constraint di database.
        // Untuk kasus nyata, mungkin perlu loop check jika ada collision (sangat jarang dengan uniqid).
        $sku = 'PROD-' . $currentBusinessUnitId . '-' . strtoupper(Str::random(8));
        // Alternatif lain: $sku = 'PROD-' . $currentBusinessUnitId . '-' . time();

        // [MODIFIKASI] Handle file upload dengan kompresi
        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            // [MODIFIKASI] Ganti ekstensi menjadi .webp untuk nama file baru
            $imageName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME) . '-' . time() . '.webp';
            
            // Ubah ukuran, konversi ke WebP dengan kualitas 75%
            $img = Image::read($image->getRealPath());
            $img->resize(1000, 1000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->toWebp(75);

            // Simpan gambar yang sudah dioptimasi
            $storagePath = storage_path('app/public/product_images/' . $imageName);
            $img->save($storagePath);
            
            $imagePath = 'product_images/' . $imageName;
        }

        // Siapkan data untuk disimpan
        $dataToStore = $validatedData;
        $dataToStore['business_unit_id'] = $currentBusinessUnitId;
        $dataToStore['sku'] = $sku;
        if ($imagePath) {
            $dataToStore['image_path'] = $imagePath;
        }
        // Handle checkbox 'is_active'
        // Jika checkbox tidak dicentang, $request->is_active tidak akan ada, jadi kita set false.
        // Jika dicentang, nilainya "1", yang akan di-cast ke true (boolean).
        $dataToStore['is_active'] = $request->has('is_active');


        Product::create($dataToStore);

        return redirect()->route('karung.products.index')
                         ->with('success', 'Produk baru berhasil ditambahkan!');
    }

    public function edit(Product $product) // Laravel otomatis mengambil data produk berdasarkan ID di URL
    {
        // TODO: Nanti kita perlu menambahkan pengecekan apakah $product ini
        // benar-benar milik business_unit_id yang sedang aktif/diizinkan untuk pengguna.

        // Ambil data untuk dropdown, sama seperti di method create()
        $categories = ProductCategory::orderBy('name', 'asc')->get();
        $types = ProductType::orderBy('name', 'asc')->get();
        $suppliers = Supplier::orderBy('name', 'asc')->get();

        return view('karung::products.edit', compact('product', 'categories', 'types', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        // Tentukan business_unit_id (sementara hardcode, nanti harus dinamis)
        // TODO: Dapatkan business_unit_id dari sesi pengguna atau instansi bisnis yang aktif
        // dan pastikan $product ini memang milik business_unit_id tersebut sebelum diupdate.
        $currentBusinessUnitId = 1;

        // Validasi data
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('karung_products', 'name')
                    ->where(function ($query) use ($currentBusinessUnitId) {
                        return $query->where('business_unit_id', $currentBusinessUnitId);
                    })
                    ->ignore($product->id), // Abaikan ID produk saat ini
            ],
            'product_category_id' => ['nullable', 'integer', 'exists:karung_product_categories,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:karung_product_types,id'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'default_supplier_id' => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'image_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // [MODIFIKASI] Handle file upload dengan kompresi
        if ($request->hasFile('image_path')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $image = $request->file('image_path');
            // [MODIFIKASI] Ganti ekstensi menjadi .webp
            $imageName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME) . '-' . time() . '.webp';
            
            $img = Image::read($image->getRealPath());
            $img->resize(1000, 1000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->toWebp(75);

            $storagePath = storage_path('app/public/product_images/' . $imageName);
            $img->save($storagePath);
            
            $validatedData['image_path'] = 'product_images/' . $imageName;
        }

        // Handle checkbox 'is_active'
        // Jika checkbox tidak dicentang, request tidak akan mengirimkan 'is_active'
        // Jadi kita set nilainya secara manual menjadi false (0)
        $validatedData['is_active'] = $request->has('is_active');

        // Lakukan update pada data produk
        $product->update($validatedData);

        return redirect()->route('karung.products.index')
                         ->with('success', 'Data produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        // TODO: Nanti kita perlu menambahkan pengecekan otorisasi,
        // dan validasi apakah produk ini sudah terhubung dengan transaksi penjualan/pembelian.
        // Untuk V1 CRUD dasar, kita langsung hapus.

        try {
            // Simpan nama dan path gambar sebelum record dihapus
            $productName = $product->name;
            $imagePath = $product->image_path;

            // Hapus record produk dari database
            $product->delete();

            // Jika ada path gambar, hapus file gambar dari storage
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->route('karung.products.index')
                             ->with('success', "Produk '{$productName}' berhasil dihapus!");
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error jika ada foreign key constraint
            return redirect()->route('karung.products.index')
                             ->with('error', "Gagal menghapus produk '{$product->name}'. Mungkin produk ini masih terhubung dengan data transaksi.");
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('karung.products.index')
                             ->with('error', "Terjadi kesalahan saat mencoba menghapus produk '{$product->name}'.");
        }
    }
    
    public function getProductGallery(Request $request)
    {
        $query = Product::where('is_active', true)->where('stock', '>', 0);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        $products = $query->latest()->simplePaginate(2);

        // [PERBAIKAN FINAL] Ubah cara membuat URL gambar di sini
        $products->getCollection()->transform(function ($product) {
            // Gunakan helper asset() yang sudah terbukti bekerja, sama seperti di halaman edit.
            $product->image_url = $product->image_path ? asset('storage/' . $product->image_path) : 'https://via.placeholder.com/150';
            return $product;
        });

        return response()->json($products);
    }

    public function bulkPriceEdit(Request $request)
    {
        // [MODIFIKASI] Ganti cara pengecekan otorisasi
        if (Gate::denies('update', new Product())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data produk.');
        }

        $categories = ProductCategory::orderBy('name')->get();
        $selectedCategoryId = $request->input('category_id');
        
        $query = Product::query();

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }
        
        if ($selectedCategoryId) {
            $query->where('product_category_id', $selectedCategoryId);
        }

        $products = $query->orderBy('name')->get();

        return view('karung::products.bulk_price_edit', compact('products', 'categories', 'selectedCategoryId'));
    }

    public function bulkPriceUpdate(Request $request)
    {
        // [MODIFIKASI] Ganti cara pengecekan otorisasi
        if (Gate::denies('update', new Product())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data produk.');
        }

        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['products'] as $productId => $newPrice) {
                Product::where('id', $productId)->update([
                    'purchase_price' => $newPrice
                ]);
            }

            DB::commit();
            
            activity()->log("Melakukan pembaruan harga beli massal untuk " . count($validated['products']) . " produk.");

            return redirect()->route('karung.products.bulk-price.edit')->with('success', 'Harga beli produk berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui harga: ' . $e->getMessage())->withInput();
        }
    }
}