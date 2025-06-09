<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\Customer;
use App\Modules\Karung\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SalesTransactionController extends Controller
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
        $query = SalesTransaction::with(['customer', 'details.product']);

        // Cek apakah ada input pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            // Tambahkan kondisi where untuk memfilter berdasarkan No. Invoice ATAU Nama Pelanggan (dari relasi)
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                      $customerQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Lanjutkan query dengan urutan dan paginasi
        $sales = $query->latest()->paginate(15);

        // Mengirim data $sales ke view
        return view('karung::sales.index', compact('sales'));
    }

    public function create()
    {
        // TODO: Nantinya, data ini (customers, products)
        // HARUS difilter berdasarkan 'business_unit_id' yang aktif.
        // Untuk sekarang, kita ambil semua dulu untuk tes.
        // $currentBusinessUnitId = 1; // Contoh hardcode

        $customers = Customer::orderBy('name', 'asc')->get(); // Ambil semua pelanggan, urutkan berdasarkan nama
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get(); // Ambil semua produk yang aktif, urutkan berdasarkan nama

        return view('karung::sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        // Aturan validasi
        $validatedData = $request->validate([
            'transaction_date'      => ['required', 'date'],
            'customer_id'           => ['nullable', 'integer', 'exists:karung_customers,id'],
            'notes'                 => ['nullable', 'string'],
            'details'               => ['required', 'array', 'min:1'], // Pastikan ada minimal 1 detail produk
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.selling_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            // Memulai Database Transaction
            DB::beginTransaction();

            // 1. Siapkan dan Simpan data Transaksi Induk (tanpa total_amount dulu)
            $saleData = [
                'business_unit_id'      => 1, // TODO: Harus dinamis
                'customer_id'           => $validatedData['customer_id'],
                'transaction_date'      => $validatedData['transaction_date'],
                'notes'                 => $validatedData['notes'],
                'user_id'               => auth()->id(),
                'invoice_number'        => 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6)), // Contoh pembuatan invoice number sederhana
            ];

            $sale = SalesTransaction::create($saleData);

            // 2. Loop dan Simpan data Detail Transaksi
            $totalAmount = 0;

            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['selling_price_at_transaction'];
                $sale->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'selling_price_at_transaction' => $detail['selling_price_at_transaction'],
                    'sub_total' => $subTotal,
                ]);

                $totalAmount += $subTotal;

                // PERJANJIAN: Untuk V1, kita TIDAK update stok produk di master data secara otomatis.
            }

            // 3. Update total_amount di Transaksi Induk
            $sale->total_amount = $totalAmount;
            $sale->save();

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('karung.sales.index')
                             ->with('success', 'Transaksi penjualan baru berhasil disimpan!');

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query yang sudah dijalankan
            DB::rollBack();

            // Tampilkan pesan error
            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat menyimpan transaksi penjualan: ' . $e->getMessage())
                            ->withInput();
        }
    }

    public function show(SalesTransaction $sale)
    {
        // TODO: Nanti kita perlu menambahkan pengecekan otorisasi,
        // memastikan pengguna hanya bisa melihat transaksi dari business_unit_id mereka.

        // Eager load relasi yang dibutuhkan untuk halaman detail
        $sale->load(['customer', 'user', 'details.product']);

        return view('karung::sales.show', compact('sale'));
    }
    
    // ... (method CRUD lainnya yang ditunda: edit, update, destroy) ...
}