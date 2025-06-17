<?php

namespace App\Exports;

use App\Modules\Karung\Models\SalesTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesTransaction::with(['customer', 'user', 'details.product'])
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
            'No. Invoice',
            'Pelanggan',
            'Dicatat Oleh',
            'Produk',
            'Jumlah',
            'Harga Satuan',
            'Subtotal',
            'Total Transaksi',
        ];
    }

    /**
     * @var SalesTransaction $sale
     */
    public function map($sale): array
    {
        $rows = [];
        $isFirstRow = true;

        foreach ($sale->details as $detail) {
            $rows[] = [
                'Tanggal Transaksi' => $isFirstRow ? $sale->transaction_date->format('d-m-Y H:i') : '',
                'No. Invoice'       => $isFirstRow ? $sale->invoice_number : '',
                'Pelanggan'         => $isFirstRow ? ($sale->customer->name ?? 'Penjualan Umum') : '',
                'Dicatat Oleh'      => $isFirstRow ? ($sale->user->name ?? 'N/A') : '',
                'Produk'            => $detail->product->name ?? 'Produk Dihapus',
                'Jumlah'            => $detail->quantity,
                'Harga Satuan'      => $detail->selling_price_at_transaction,
                'Subtotal'          => $detail->sub_total,
                'Total Transaksi'   => $isFirstRow ? $sale->total_amount : '',
            ];
            $isFirstRow = false;
        }

        return $rows;
    }
}