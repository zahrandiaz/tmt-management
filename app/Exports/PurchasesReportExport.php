<?php

namespace App\Exports;

use App\Modules\Karung\Models\PurchaseTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PurchasesReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = PurchaseTransaction::with(['supplier', 'user', 'details.product'])
                                    ->where('status', 'Completed');

        if ($this->startDate) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($this->startDate));
        }
        if ($this->endDate) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($this->endDate));
        }

        return $query->latest('transaction_date')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal Transaksi',
            'Kode Pembelian',
            'No. Referensi',
            'Supplier',
            'Dicatat Oleh',
            'Produk',
            'Jumlah',
            'Harga Beli Satuan',
            'Subtotal',
            'Total Transaksi',
        ];
    }

    /**
     * @var PurchaseTransaction $purchase
     */
    public function map($purchase): array
    {
        $rows = [];
        $isFirstRow = true;

        foreach ($purchase->details as $detail) {
            $rows[] = [
                'Tanggal Transaksi' => $isFirstRow ? $purchase->transaction_date->format('d-m-Y H:i') : '',
                'Kode Pembelian'    => $isFirstRow ? $purchase->purchase_code : '',
                'No. Referensi'     => $isFirstRow ? $purchase->purchase_reference_no : '',
                'Supplier'          => $isFirstRow ? ($purchase->supplier->name ?? 'Pembelian Umum') : '',
                'Dicatat Oleh'      => $isFirstRow ? ($purchase->user->name ?? 'N/A') : '',
                'Produk'            => $detail->product->name ?? 'Produk Dihapus',
                'Jumlah'            => $detail->quantity,
                'Harga Beli Satuan' => $detail->purchase_price_at_transaction,
                'Subtotal'          => $detail->sub_total,
                'Total Transaksi'   => $isFirstRow ? $purchase->total_amount : '',
            ];
            $isFirstRow = false;
        }

        return $rows;
    }
}