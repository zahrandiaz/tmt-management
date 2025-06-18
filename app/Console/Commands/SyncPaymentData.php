<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\PurchaseTransaction;
use Illuminate\Support\Facades\DB;

class SyncPaymentData extends Command
{
    protected $signature = 'app:sync-payment-data';
    protected $description = 'Sinkronisasi data amount_paid untuk transaksi lama yang statusnya lunas.';

    public function handle()
    {
        $this->info('Memulai sinkronisasi data pembayaran...');

        DB::transaction(function () {
            // Sinkronisasi Transaksi Penjualan
            $salesToUpdate = SalesTransaction::where('status', 'Completed')
                ->where('payment_status', 'Lunas')
                ->whereNull('amount_paid')
                ->get();

            foreach ($salesToUpdate as $sale) {
                $sale->amount_paid = $sale->total_amount;
                $sale->save();
            }
            $this->info($salesToUpdate->count() . ' data penjualan berhasil disinkronkan.');

            // Sinkronisasi Transaksi Pembelian
            $purchasesToUpdate = PurchaseTransaction::where('status', 'Completed')
                ->where('payment_status', 'Lunas')
                ->whereNull('amount_paid')
                ->get();

            foreach ($purchasesToUpdate as $purchase) {
                $purchase->amount_paid = $purchase->total_amount;
                $purchase->save();
            }
            $this->info($purchasesToUpdate->count() . ' data pembelian berhasil disinkronkan.');
        });

        $this->info('Sinkronisasi data pembayaran selesai!');
        return 0;
    }
}