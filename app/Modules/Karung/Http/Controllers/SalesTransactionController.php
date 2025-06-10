<?php
// File: app/Modules/Karung/Http/Controllers/SalesTransactionController.php

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
        // 1. Ambil status dari URL, jika tidak ada, defaultnya adalah 'Completed'
        $status = $request->query('status', 'Completed');

        // TODO: Filter berdasarkan business_unit_id yang aktif.
        $query = SalesTransaction::with(['customer', 'details.product'])
                                    // 2. Filter berdasarkan status yang dipilih
                                    ->where('status', $status);

        // Cek apakah ada input pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                      $customerQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Lanjutkan query dengan urutan dan paginasi
        $sales = $query->latest()->paginate(15);

        // 3. Kirim variabel $status ke view agar view tahu tab mana yang aktif
        return view('karung::sales.index', compact('sales', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name', 'asc')->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('karung::sales.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Aturan validasi
        $validatedData = $request->validate([
            'transaction_date'      => ['required', 'date'],
            'customer_id'           => ['nullable', 'integer', 'exists:karung_customers,id'],
            'notes'                 => ['nullable', 'string'],
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.selling_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();
            
            // --- BAGIAN YANG DIPERBARUI DIMULAI DI SINI ---
            $customerId = $validatedData['customer_id'];

            // Jika tidak ada pelanggan yang dipilih, cari ID "Pelanggan Umum"
            if (is_null($customerId)) {
                $defaultCustomer = Customer::where('name', 'Pelanggan Umum')->first();
                $customerId = $defaultCustomer?->id;
            }
            // --- AKHIR BAGIAN YANG DIPERBARUI ---


            // 1. Siapkan dan Simpan data Transaksi Induk
            $saleData = [
                'business_unit_id'      => 1, // TODO: Harus dinamis
                'customer_id'           => $customerId, // Menggunakan variabel $customerId yang sudah diproses
                'transaction_date'      => $validatedData['transaction_date'],
                'notes'                 => $validatedData['notes'],
                'user_id'               => auth()->id(),
                'invoice_number'        => 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6)),
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
            }

            // 3. Update total_amount di Transaksi Induk
            $sale->total_amount = $totalAmount;
            $sale->save();

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return redirect()->route('karung.sales.index')
                             ->with('success', 'Transaksi penjualan baru berhasil disimpan!');

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query
            DB::rollBack();

            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat menyimpan transaksi penjualan: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesTransaction $sale)
    {
        $sale->load(['customer', 'user', 'details.product']);
        return view('karung::sales.show', compact('sale'));
    }
    
    /**
     * Cancel the specified transaction.
     */
    public function cancel(SalesTransaction $sale)
    {
        if ($sale->status == 'Cancelled') {
            return redirect()->route('karung.sales.index')
                             ->with('error', 'Transaksi ini sudah pernah dibatalkan sebelumnya.');
        }

        $sale->status = 'Cancelled';
        $sale->save();
        
        return redirect()->route('karung.sales.index')
                         ->with('success', "Transaksi penjualan dengan invoice #{$sale->invoice_number} berhasil dibatalkan.");
    }
}