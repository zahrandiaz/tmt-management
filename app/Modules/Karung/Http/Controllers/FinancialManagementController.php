<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialManagementController extends Controller
{
    /**
     * Menampilkan halaman manajemen piutang (penjualan belum lunas).
     */
    public function receivables()
    {
        $this->authorize('viewAny', SalesTransaction::class); // Asumsi hak akses sama dengan lihat transaksi

        $receivables = SalesTransaction::with('customer')
            ->where('payment_status', 'Belum Lunas')
            ->latest('transaction_date')
            ->paginate(20);

        return view('karung::financials.receivables', compact('receivables'));
    }

    /**
     * Menampilkan halaman manajemen utang (pembelian belum lunas).
     */
    public function payables()
    {
        $this->authorize('viewAny', PurchaseTransaction::class); // Asumsi hak akses sama dengan lihat transaksi

        $payables = PurchaseTransaction::with('supplier')
            ->where('payment_status', 'Belum Lunas')
            ->latest('transaction_date')
            ->paginate(20);

        return view('karung::financials.payables', compact('payables'));
    }

     /**
     * Menampilkan riwayat pembayaran untuk satu transaksi spesifik.
     */
    public function paymentHistory($type, $id)
    {
        $transaction = null;
        $modelClass = null;

        if ($type === 'sales') {
            $modelClass = SalesTransaction::class;
            // Eager load HANYA relasi yang relevan
            $transaction = $modelClass::with('customer')->findOrFail($id);
            $this->authorize('view', $transaction);

        } elseif ($type === 'purchase') {
            $modelClass = PurchaseTransaction::class;
            // Eager load HANYA relasi yang relevan
            $transaction = $modelClass::with('supplier')->findOrFail($id);
            $this->authorize('view', $transaction);

        } else {
            abort(404);
        }
        
        $paymentHistories = PaymentHistory::where('transaction_type', $type)
            ->where('transaction_id', $id)
            ->latest('payment_date')
            ->with('user') // [OPTIMASI] Eager load data user pencatat
            ->get();

        return view('karung::financials.payment_history', compact('transaction', 'paymentHistories', 'type'));
    }

    /**
     * Menyimpan data pembayaran baru.
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|integer',
            'transaction_type' => 'required|string|in:sales,purchase',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $transaction = null;
                $modelClass = null;

                if ($request->transaction_type === 'sales') {
                    $modelClass = SalesTransaction::class;
                } else {
                    $modelClass = PurchaseTransaction::class;
                }

                // Kunci record transaksi untuk mencegah race condition
                $transaction = $modelClass::findOrFail($request->transaction_id);

                // 1. Simpan riwayat pembayaran
                PaymentHistory::create([
                    'transaction_id' => $transaction->id,
                    'transaction_type' => $request->transaction_type,
                    'payment_date' => $request->payment_date,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'notes' => $request->notes,
                    'user_id' => auth()->id(),
                ]);

                // 2. Update total yang sudah dibayar pada transaksi induk
                $transaction->amount_paid += $request->amount;

                // 3. Cek dan update status lunas
                if ($transaction->amount_paid >= $transaction->total_amount) {
                    $transaction->payment_status = 'Lunas';
                }

                $transaction->save();
            });

            return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');

        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }
}