<?php

namespace App\Exports;

use App\Modules\Karung\Models\PurchaseTransactionDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Carbon\Carbon;

class PurchasesReportExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    protected $startDate;
    protected $endDate;
    protected $supplierId;

    public function __construct($startDate, $endDate, $supplierId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->supplierId = $supplierId;
    }

    /**
    * @return \Illuminate\Database\Query\Builder
    */
    public function query()
    {
        $query = PurchaseTransactionDetail::with(['transaction.supplier', 'transaction.user', 'product'])
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'Completed');

                if ($this->startDate) {
                    $q->whereDate('transaction_date', '>=', Carbon::parse($this->startDate));
                }
                if ($this->endDate) {
                    $q->whereDate('transaction_date', '<=', Carbon::parse($this->endDate));
                }
                if ($this->supplierId) {
                    $q->where('supplier_id', $this->supplierId);
                }
            });
        
        return $query->orderBy(
            \App\Modules\Karung\Models\PurchaseTransaction::select('transaction_date')
                ->whereColumn('karung_purchase_transactions.id', 'karung_purchase_transaction_details.purchase_transaction_id')
        );
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'Kode Pembelian',
            'No. Referensi',
            'Tanggal Transaksi',
            'Supplier',
            'Dicatat Oleh',
            'Metode Pembayaran',
            'Status Pembayaran',
            'SKU Produk',
            'Nama Produk',
            'Kuantitas',
            'Harga Beli Satuan',
            'Subtotal',
        ];
    }

    /**
    * @param PurchaseTransactionDetail $detail
    */
    public function map($detail): array
    {
        return [
            $detail->transaction->purchase_code,
            $detail->transaction->purchase_reference_no,
            $detail->transaction->transaction_date->format('Y-m-d H:i:s'),
            $detail->transaction->supplier->name ?? 'Pembelian Umum',
            $detail->transaction->user->name ?? 'N/A',
            $detail->transaction->payment_method,
            $detail->transaction->payment_status,
            $detail->product->sku ?? 'N/A',
            $detail->product->name ?? 'Produk Dihapus',
            $detail->quantity,
            $detail->purchase_price_at_transaction,
            $detail->sub_total,
        ];
    }
}