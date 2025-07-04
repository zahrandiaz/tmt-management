<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\ModuleBaseController;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use App\Models\PaymentHistory;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class FinancialManagementController extends ModuleBaseController
{
    protected $paymentService; // [BARU v1.32.1]

    public function __construct(PaymentService $paymentService) // [MODIFIKASI v1.32.1]
    {
        $this->paymentService = $paymentService; // [BARU v1.32.1]
    }

    public function receivables()
    {
        $this->authorize('viewAny', SalesTransaction::class);
        $receivables = SalesTransaction::with('customer')
            ->where('payment_status', 'Belum Lunas')
            ->latest('transaction_date')
            ->paginate(20);
        return view('karung::financials.receivables', compact('receivables'));
    }

    public function payables()
    {
        $this->authorize('viewAny', PurchaseTransaction::class);
        $payables = PurchaseTransaction::with('supplier')
            ->where('payment_status', 'Belum Lunas')
            ->latest('transaction_date')
            ->paginate(20);
        return view('karung::financials.payables', compact('payables'));
    }

    public function paymentHistory($type, $id)
    {
        $transaction = null;
        $modelClass = null;

        if ($type === 'sales') {
            $modelClass = SalesTransaction::class;
            $transaction = $modelClass::with('customer')->findOrFail($id);
            $this->authorize('view', $transaction);
        } elseif ($type === 'purchase') {
            $modelClass = PurchaseTransaction::class;
            $transaction = $modelClass::with('supplier')->findOrFail($id);
            $this->authorize('view', $transaction);
        } else {
            abort(404);
        }
        
        $paymentHistories = PaymentHistory::where('transaction_type', $type)
            ->where('transaction_id', $id)
            ->latest('payment_date')
            ->with('user')
            ->get();

        return view('karung::financials.payment_history', compact('transaction', 'paymentHistories', 'type'));
    }

    public function storePayment(Request $request)
    {
        try {
            // [REFACTOR v1.32.1] Panggil service untuk menangani semua logika
            $this->paymentService->recordPayment($request);
            return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            report($e); // Laporkan error untuk debugging
            return redirect()->back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }
}