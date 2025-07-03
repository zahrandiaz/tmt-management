<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\SalesReturn;
use App\Modules\Karung\Http\Requests\StoreSalesReturnRequest;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\PurchaseReturn;
use App\Modules\Karung\Http\Requests\StorePurchaseReturnRequest;
use App\Modules\Karung\Services\StockManagementService;
use App\Modules\Karung\Models\Setting; // <-- [BARU]
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <-- [BARU]

class ReturnController extends Controller
{
    // ... (property dan method __construct tidak berubah) ...
    protected $stockManagementService;

    public function __construct(StockManagementService $stockManagementService)
    {
        $this->stockManagementService = $stockManagementService;
    }

    // ... (semua method untuk sales return dan purchase return yang sudah ada tidak berubah) ...
    /**
     * Menampilkan daftar retur penjualan.
     */
    public function salesReturnIndex()
    {
        $this->authorize('viewAny', SalesReturn::class); // Kita akan buat Policy nanti

        $returns = SalesReturn::with('customer', 'originalTransaction')->latest()->paginate(15);
        return view('karung::returns.sales.index', compact('returns'));
    }

    /**
     * Menampilkan form untuk membuat retur dari transaksi penjualan.
     */
    public function createSalesReturn(SalesTransaction $salesTransaction)
    {
        $this->authorize('create', SalesReturn::class);

        // Load detail transaksi beserta nama produk
        $salesTransaction->load('details.product');

        return view('karung::returns.sales.create', compact('salesTransaction'));
    }

    /**
     * Menyimpan data retur penjualan baru.
     */
    public function storeSalesReturn(StoreSalesReturnRequest $request, SalesTransaction $salesTransaction)
    {
        try {
            $validated = $request->validated();
            $totalReturnAmount = 0;

            $return = DB::transaction(function () use ($validated, $salesTransaction, &$totalReturnAmount) {
                // ... (bagian pembuatan retur utama dan detail tidak berubah) ...
                $salesReturn = SalesReturn::create([
                    'return_code' => 'RTS-' . Carbon::now()->format('YmdHis'),
                    'sales_transaction_id' => $salesTransaction->id,
                    'customer_id' => $salesTransaction->customer_id,
                    'user_id' => auth()->id(),
                    'return_date' => $validated['return_date'],
                    'reason' => $validated['reason'],
                    'total_amount' => 0,
                ]);

                foreach ($validated['items'] as $item) {
                    $originalDetail = $salesTransaction->details()->find($item['sales_transaction_detail_id']);
                    if (!$originalDetail || $item['return_quantity'] > $originalDetail->quantity) {
                        throw new \Exception("Jumlah retur untuk produk {$originalDetail->product->name} melebihi jumlah pembelian.");
                    }
                    $subtotal = $originalDetail->selling_price_at_transaction * $item['return_quantity'];
                    $totalReturnAmount += $subtotal;
                    $salesReturn->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['return_quantity'],
                        'price' => $originalDetail->selling_price_at_transaction,
                        'subtotal' => $subtotal,
                    ]);
                }

                $salesReturn->total_amount = $totalReturnAmount;
                $salesReturn->save();
                $this->stockManagementService->handleSaleReturn($salesReturn);

                // =====================================================================
                // [REVISI LOGIKA v1.30] Penyesuaian piutang dengan mengurangi total transaksi
                // =====================================================================
                // 1. Kurangi total nilai transaksi dengan nilai retur.
                $salesTransaction->total_amount -= $totalReturnAmount;
                
                // 2. Cek kembali apakah transaksi sekarang menjadi lunas.
                //    Kolom 'amount_paid' tidak diubah sama sekali.
                if ($salesTransaction->amount_paid >= $salesTransaction->total_amount) {
                    $salesTransaction->payment_status = 'Lunas';
                }
                
                $salesTransaction->save();
                // =====================================================================

                return $salesReturn;
            });

            return redirect()->route('karung.returns.sales.show', $return->id)
                ->with('success', 'Retur penjualan berhasil dibuat dan piutang telah disesuaikan. Kode: ' . $return->return_code);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat retur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail satu retur penjualan.
     */
    public function showSalesReturn(SalesReturn $salesReturn)
    {
        $this->authorize('view', $salesReturn);

        $salesReturn->load('details.product', 'customer', 'user', 'originalTransaction');
        return view('karung::returns.sales.show', compact('salesReturn'));
    }

    // --- [BARU v1.28] PURCHASE RETURN METHODS ---

    /**
     * Menampilkan daftar retur pembelian.
     */
    public function purchaseReturnIndex()
    {
        $this->authorize('viewAny', PurchaseReturn::class);

        $returns = PurchaseReturn::with('supplier', 'originalTransaction')->latest()->paginate(15);
        return view('karung::returns.purchases.index', compact('returns'));
    }

    /**
     * Menampilkan form untuk membuat retur dari transaksi pembelian.
     */
    public function createPurchaseReturn(PurchaseTransaction $purchaseTransaction)
    {
        $this->authorize('create', PurchaseReturn::class);

        // Gunakan kode ini yang sudah terbukti bisa memuat relasi
        $loadedTransaction = PurchaseTransaction::with('details.product')
            ->findOrFail($purchaseTransaction->id);

        return view('karung::returns.purchases.create', ['purchaseTransaction' => $loadedTransaction]);
    }

    /**
     * Menyimpan data retur pembelian baru.
     */
    public function storePurchaseReturn(StorePurchaseReturnRequest $request, PurchaseTransaction $purchaseTransaction)
    {
        try {
            $validated = $request->validated();
            $totalReturnAmount = 0;

            $return = DB::transaction(function () use ($validated, $purchaseTransaction, &$totalReturnAmount) {
                $purchaseReturn = PurchaseReturn::create([
                    'return_code' => 'RTP-' . Carbon::now()->format('YmdHis'),
                    'purchase_transaction_id' => $purchaseTransaction->id,
                    'supplier_id' => $purchaseTransaction->supplier_id,
                    'user_id' => auth()->id(),
                    'return_date' => $validated['return_date'],
                    'reason' => $validated['reason'],
                    'total_amount' => 0, // Placeholder
                ]);

                foreach ($validated['items'] as $item) {
                    // [MODIFIKASI] Cari detail langsung dari model, bukan dari relasi.
                    $originalDetail = \App\Modules\Karung\Models\PurchaseTransactionDetail::find($item['purchase_transaction_detail_id']);

                    // Cek ulang untuk keamanan, meskipun sudah divalidasi di FormRequest
                    if (!$originalDetail || $originalDetail->purchase_transaction_id !== $purchaseTransaction->id) {
                        throw new \Exception("Terjadi inkonsistensi data item retur.");
                    }
                    if ($item['return_quantity'] > $originalDetail->quantity) {
                        throw new \Exception("Jumlah retur melebihi jumlah pembelian.");
                    }

                    // [MODIFIKASI] Gunakan purchase_price_at_transaction dari detail asli
                    $subtotal = $originalDetail->purchase_price_at_transaction * $item['return_quantity'];
                    $totalReturnAmount += $subtotal;

                    $purchaseReturn->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['return_quantity'],
                        // [MODIFIKASI] Gunakan purchase_price_at_transaction dari detail asli
                        'price' => $originalDetail->purchase_price_at_transaction,
                        'subtotal' => $subtotal,
                    ]);
                }

                $purchaseReturn->total_amount = $totalReturnAmount;
                $purchaseReturn->save();

                $this->stockManagementService->handlePurchaseReturn($purchaseReturn);
                
                // TODO: Logika penyesuaian pembayaran pada tagihan asli ke supplier.

                return $purchaseReturn;
            });

            return redirect()->route('karung.returns.purchases.show', $return->id)
                ->with('success', 'Retur pembelian berhasil dibuat dengan kode: ' . $return->return_code);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat retur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail satu retur pembelian.
     */
    public function showPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $this->authorize('view', $purchaseReturn);
        $purchaseReturn->load('details.product', 'supplier', 'user', 'originalTransaction');
        return view('karung::returns.purchases.show', compact('purchaseReturn'));
    }

    /**
     * [BARU v1.30] Generate dan download Nota Kredit dalam format PDF.
     */
    public function downloadCreditNotePdf(SalesReturn $salesReturn)
    {
        $this->authorize('view', $salesReturn);
        $salesReturn->load('details.product', 'customer', 'originalTransaction');
        
        // Ambil pengaturan toko untuk header
        $settings = Setting::pluck('setting_value', 'setting_key');

        $pdf = Pdf::loadView('karung::returns.sales.credit_note_pdf', compact('salesReturn', 'settings'));
        
        $fileName = 'nota-kredit-' . strtolower($salesReturn->return_code) . '.pdf';
        
        return $pdf->stream($fileName);
    }
}