<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\Supplier;
use App\Modules\Karung\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class PurchaseTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // TODO: Filter berdasarkan business_unit_id yang aktif.
        // $currentBusinessUnitId = 1;

        // Mulai query dengan eager loading
        // DIUBAH: Kita ganti 'user' menjadi 'details.product' untuk mengambil nama produk
        $query = PurchaseTransaction::with(['supplier', 'details.product']);

        // Cek apakah ada input pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            // Tambahkan kondisi where untuk memfilter berdasarkan No. Referensi ATAU Nama Supplier (dari relasi)
            $query->where(function ($q) use ($searchTerm) {
                $q->where('purchase_reference_no', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('supplier', function ($supplierQuery) use ($searchTerm) {
                      $supplierQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Lanjutkan query dengan urutan dan paginasi
        $purchases = $query->latest('transaction_date')->paginate(15);

        // Mengirim data $purchases ke view
        return view('karung::purchases.index', compact('purchases'));
    }

    public function create()
    {
        // TODO: Nantinya, semua data ini (suppliers, products)
        // HARUS difilter berdasarkan 'business_unit_id' yang aktif.
        // Untuk sekarang, kita ambil semua dulu untuk tes.
        // $currentBusinessUnitId = 1; // Contoh hardcode

        $suppliers = Supplier::orderBy('name', 'asc')->get(); // Ambil semua supplier, urutkan berdasarkan nama
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get(); // Ambil semua produk yang aktif, urutkan berdasarkan nama

        return view('karung::purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        // Aturan validasi
        $validatedData = $request->validate([
            'transaction_date'      => ['required', 'date'],
            'supplier_id'           => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'purchase_reference_no' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'attachment_path'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'details'               => ['required', 'array', 'min:1'], // Pastikan ada minimal 1 detail produk
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.purchase_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            // Memulai Database Transaction
            DB::beginTransaction();

            // 1. Siapkan dan Simpan data Transaksi Induk (tanpa total_amount dulu)
            $purchaseData = [
                'business_unit_id'      => 1, // TODO: Harus dinamis
                'supplier_id'           => $validatedData['supplier_id'],
                'transaction_date'      => $validatedData['transaction_date'],
                'purchase_reference_no' => $validatedData['purchase_reference_no'],
                'notes'                 => $validatedData['notes'],
                'user_id'               => auth()->id(), // ID pengguna yang sedang login
            ];

            // Handle upload file jika ada
            if ($request->hasFile('attachment_path')) {
                $purchaseData['attachment_path'] = $request->file('attachment_path')->store('purchase_attachments', 'public');
            }

            $purchase = PurchaseTransaction::create($purchaseData);

            // 2. Loop dan Simpan data Detail Transaksi
            $totalAmount = 0;

            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['purchase_price_at_transaction'];
                $purchase->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'purchase_price_at_transaction' => $detail['purchase_price_at_transaction'],
                    'sub_total' => $subTotal,
                ]);

                $totalAmount += $subTotal;

                // PERJANJIAN: Untuk V1, kita TIDAK update stok produk di master data secara otomatis.
                // Jika nanti fitur stok otomatis diaktifkan, kodenya akan ada di sini.
            }

            // 3. Update total_amount di Transaksi Induk
            $purchase->total_amount = $totalAmount;
            $purchase->save();

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('karung.purchases.index')
                             ->with('success', 'Transaksi pembelian baru berhasil disimpan!');

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query yang sudah dijalankan
            DB::rollBack();

            // Tampilkan pesan error
            return redirect()->back()
                             ->with('error', 'Terjadi kesalahan saat menyimpan transaksi pembelian: ' . $e->getMessage())
                             ->withInput();
        }
    }

    public function show(PurchaseTransaction $purchase)
    {
        // TODO: Nanti kita perlu menambahkan pengecekan otorisasi,
        // memastikan pengguna hanya bisa melihat transaksi dari business_unit_id mereka.

        // Eager load relasi yang dibutuhkan untuk halaman detail
        $purchase->load(['supplier', 'user', 'details.product']);

        return view('karung::purchases.show', compact('purchase'));
    }

    // ... (method CRUD lainnya: edit, update, destroy) ...
}