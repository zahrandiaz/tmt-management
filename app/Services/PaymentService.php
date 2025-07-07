<?php

namespace App\Services;

use App\Models\PaymentHistory;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Mencatat pembayaran baru untuk transaksi penjualan atau pembelian.
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function recordPayment(Request $request): void
    {
        $validated = $request->validate([
            'transaction_id' => 'required|integer',
            'transaction_type' => 'required|string|in:sales,purchase',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $modelClass = $validated['transaction_type'] === 'sales'
                ? SalesTransaction::class
                : PurchaseTransaction::class;

            // Kunci record transaksi untuk mencegah race condition
            $transaction = $modelClass::findOrFail($validated['transaction_id']);

            // 1. Simpan riwayat pembayaran
            PaymentHistory::create([
                'transaction_id' => $transaction->id,
                'transaction_type' => $validated['transaction_type'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
            ]);

            // 2. Update total yang sudah dibayar pada transaksi induk
            $transaction->amount_paid += $validated['amount'];

            // 3. Cek dan update status lunas
            if ($transaction->amount_paid >= $transaction->total_amount) {
                $transaction->payment_status = 'Lunas';
            }

            $transaction->save();
            
            activity()->log("Mencatat pembayaran sejumlah {$validated['amount']} untuk transaksi {$validated['transaction_type']} #{$transaction->id}");
        });
    }
}