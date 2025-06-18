<?php

namespace App\Exports;

use App\Modules\Karung\Models\SalesTransactionDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Carbon\Carbon;

// [MODIFIKASI] Menggunakan FromQuery untuk efisiensi & WithStrictNullComparison untuk kompatibilitas
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

    /**
    * @return \Illuminate\Database\Query\Builder
    */
    public function query()
    {
        // Query langsung ke detail, bukan transaksi induk
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
        
        // Urutkan berdasarkan tanggal transaksi induknya
        return $query->orderBy(
            \App\Modules\Karung\Models\SalesTransaction::select('transaction_date')
                ->whereColumn('karung_sales_transactions.id', 'karung_sales_transaction_details.sales_transaction_id')
        );
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Header baru untuk format flat file
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
            'Harga Modal Satuan',
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
        // Mapping data untuk setiap baris detail
        $modalPerPcs = $detail->product->purchase_price ?? 0;
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
            $modalPerPcs,
            $detail->selling_price_at_transaction,
            $detail->sub_total,
            $subLaba,
        ];
    }
}