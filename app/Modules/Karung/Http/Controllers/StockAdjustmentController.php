<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\ModuleBaseController;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Modules\Karung\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockAdjustmentController extends ModuleBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data penyesuaian, urutkan dari yang terbaru
        // Gunakan 'with' untuk eager loading agar query lebih efisien
        $adjustments = StockAdjustment::with(['product', 'user'])
                                        ->latest()
                                        ->paginate(20); // Tampilkan 20 data per halaman

        return view('karung::stock-adjustments.index', compact('adjustments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('karung::stock-adjustments.create', compact('products'));
    }

    /**
     * [MODIFIKASI] Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input dari Form
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:karung_products,id'],
            'type' => ['required', 'string', Rule::in(['Stok Opname', 'Barang Rusak', 'Barang Hilang', 'Koreksi Data', 'Lainnya'])],
            'quantity' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            // 2. Gunakan Transaksi Database untuk Keamanan Data
            DB::transaction(function () use ($validated) {
                $product = Product::findOrFail($validated['product_id']);
                $stock_before = $product->stock;
                $adjustment_value = 0;
                $stock_after = 0;

                // 3. Logika Perhitungan Stok Berdasarkan Tipe
                switch ($validated['type']) {
                    case 'Stok Opname':
                        $stock_after = $validated['quantity'];
                        $adjustment_value = $stock_after - $stock_before;
                        break;
                    
                    case 'Barang Rusak':
                    case 'Barang Hilang':
                        // Menggunakan abs() untuk memastikan nilainya selalu positif, lalu dikurangi
                        $adjustment_value = -abs($validated['quantity']);
                        $stock_after = $stock_before + $adjustment_value;
                        break;

                    case 'Koreksi Data':
                    case 'Lainnya':
                        $adjustment_value = $validated['quantity'];
                        $stock_after = $stock_before + $adjustment_value;
                        break;
                }

                // Validasi agar stok tidak menjadi negatif
                if ($stock_after < 0) {
                    // throw \Illuminate\Validation\ValidationException::withMessages([
                    //    'quantity' => 'Penyesuaian tidak valid, stok akhir tidak boleh negatif.',
                    // ]);
                }
                
                // 4. Update Stok di Tabel Produk
                $product->update(['stock' => $stock_after]);

                // 5. Buat Catatan Riwayat Penyesuaian
                StockAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => $validated['type'],
                    'quantity' => $adjustment_value,
                    'stock_before' => $stock_before,
                    'stock_after' => $stock_after,
                    'reason' => $validated['reason'],
                ]);
            });

        } catch (\Exception $e) {
            // Jika terjadi error, kembalikan dengan pesan error
            return redirect()->back()->with('error', 'Gagal menyimpan penyesuaian stok: ' . $e->getMessage())->withInput();
        }

        // 6. Redirect dengan Pesan Sukses
        return redirect()->route('karung.stock-adjustments.index')
                         ->with('success', 'Penyesuaian stok berhasil disimpan.');
    }
}