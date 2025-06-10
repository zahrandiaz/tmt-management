<?php
// File: app/Modules/Karung/Http/Controllers/PurchaseTransactionController.php

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
        // 1. Ambil status dari URL, jika tidak ada, defaultnya adalah 'Completed'
        $status = $request->query('status', 'Completed');

        // TODO: Filter berdasarkan business_unit_id yang aktif.
        $query = PurchaseTransaction::with(['supplier', 'details.product'])
                                    // 2. Filter berdasarkan status yang dipilih
                                    ->where('status', $status);

        // Cek apakah ada input pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('purchase_reference_no', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('supplier', function ($supplierQuery) use ($searchTerm) {
                      $supplierQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Lanjutkan query dengan urutan dan paginasi
        $purchases = $query->latest('transaction_date')->paginate(15);

        // 3. Kirim variabel $status ke view agar view tahu tab mana yang aktif
        return view('karung::purchases.index', compact('purchases', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('karung::purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Aturan validasi
        $validatedData = $request->validate([
            'transaction_date'      => ['required', 'date'],
            'supplier_id'           => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'purchase_reference_no' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'attachment_path'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.purchase_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            // Memulai Database Transaction
            DB::beginTransaction();

            // --- BAGIAN YANG DIPERBARUI DIMULAI DI SINI ---
            $supplierId = $validatedData['supplier_id'];

            // Jika tidak ada supplier yang dipilih, cari ID "Pembelian Umum"
            if (is_null($supplierId)) {
                $defaultSupplier = Supplier::where('name', 'Pembelian Umum')->first();
                $supplierId = $defaultSupplier?->id; // Gunakan ID-nya jika ditemukan
            }
            // --- AKHIR BAGIAN YANG DIPERBARUI ---

            // 1. Siapkan dan Simpan data Transaksi Induk
            $purchaseData = [
                'business_unit_id'      => 1, // TODO: Harus dinamis
                'supplier_id'           => $supplierId, // Menggunakan variabel $supplierId yang sudah diproses
                'transaction_date'      => $validatedData['transaction_date'],
                'purchase_reference_no' => $validatedData['purchase_reference_no'],
                'notes'                 => $validatedData['notes'],
                'user_id'               => auth()->id(),
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
            }

            // 3. Update total_amount di Transaksi Induk
            $purchase->total_amount = $totalAmount;
            $purchase->save();

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('karung.purchases.index')
                             ->with('success', 'Transaksi pembelian baru berhasil disimpan!');

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query
            DB::rollBack();

            return redirect()->back()
                             ->with('error', 'Terjadi kesalahan saat menyimpan transaksi pembelian: ' . $e->getMessage())
                             ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseTransaction $purchase)
    {
        $purchase->load(['supplier', 'user', 'details.product']);
        return view('karung::purchases.show', compact('purchase'));
    }

    /**
     * Cancel the specified transaction.
     */
    public function cancel(PurchaseTransaction $purchase)
    {
        // Pengecekan agar transaksi yang sudah dibatalkan tidak bisa dibatalkan lagi.
        if ($purchase->status == 'Cancelled') {
            return redirect()->route('karung.purchases.index')
                             ->with('error', 'Transaksi ini sudah pernah dibatalkan sebelumnya.');
        }

        $purchase->status = 'Cancelled';
        $purchase->save();
        
        return redirect()->route('karung.purchases.index')
                         ->with('success', "Transaksi pembelian dengan referensi '{$purchase->purchase_reference_no}' berhasil dibatalkan.");
    }
}