<?php

namespace App\Exports;

use App\Modules\Karung\Models\SalesTransactionDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Carbon\Carbon;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    protected $startDate;
    protected $endDate;
    protected $customerId;
    protected $userId;

    public function __construct($startDate, $endDate, $customerId, $userId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->customerId = $customerId;
        $this->userId = $userId;
    }

    public function query()
    {
        $query = SalesTransactionDetail::with(['transaction.customer', 'transaction.user', 'product'])
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'Completed');

                if ($this->startDate) {
                    $q->whereDate('transaction_date', '>=', Carbon::parse($this->startDate));
                }
                if ($this->endDate) {
                    $q->whereDate('transaction_date', '<=', Carbon::parse($this->endDate));
                }
                if ($this->customerId) {
                    $q->where('customer_id', $this->customerId);
                }
                if ($this->userId) {
                    $q->where('user_id', $this->userId);
                }
            });
        
        return $query->orderBy(
            \App\Modules\Karung\Models\SalesTransaction::select('transaction_date')
                ->whereColumn('karung_sales_transactions.id', 'karung_sales_transaction_details.sales_transaction_id')
        );
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'Tanggal Transaksi',
            'Pelanggan',
            'Kasir',
            'Metode Pembayaran',
            'Status Pembayaran',
            'SKU Produk',
            'Nama Produk',
            'Kuantitas',
            'Harga Modal Satuan (HPP)', // Label diubah untuk kejelasan
            'Harga Jual Satuan',
            'Subtotal Penjualan',
            'Subtotal Laba',
        ];
    }

    /**
     * @param SalesTransactionDetail $detail
     */
    public function map($detail): array
    {
        // [MODIFIKASI] Gunakan HPP historis yang akurat
        $modalPerPcs = $detail->purchase_price_at_sale ?? 0;
        $subLaba = ($detail->selling_price_at_transaction - $modalPerPcs) * $detail->quantity;

        return [
            $detail->transaction->invoice_number,
            $detail->transaction->transaction_date->format('Y-m-d H:i:s'),
            $detail->transaction->customer->name ?? 'Penjualan Umum',
            $detail->transaction->user->name ?? 'N/A',
            $detail->transaction->payment_method,
            $detail->transaction->payment_status,
            $detail->product->sku ?? 'N/A',
            $detail->product->name ?? 'Produk Dihapus',
            $detail->quantity,
            $modalPerPcs, // Data modal sekarang akurat
            $detail->selling_price_at_transaction,
            $detail->sub_total,
            $subLaba, // Data laba sekarang akurat
        ];
    }
}