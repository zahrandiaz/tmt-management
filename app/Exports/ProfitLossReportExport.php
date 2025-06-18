<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProfitLossReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Laporan Laba Rugi';
    }

    public function headings(): array
    {
        // Header untuk bagian paling detail (rincian item)
        return [
            'Tipe', 'Keterangan', 'Nilai'
        ];
    }

    /**
    * @return array
    */
    public function array(): array
    {
        $rows = [];

        // Bagian Ringkasan Utama
        $rows[] = ['RINGKASAN UTAMA', '', ''];
        $rows[] = ['', 'Total Pendapatan (Omzet)', $this->data['totalRevenue']];
        $rows[] = ['', 'Total Modal (HPP)', $this->data['totalCost']];
        $rows[] = ['', 'Laba Kotor', $this->data['grossProfit']];
        $rows[] = ['', '', '']; // Spasi

        // Bagian Laba per Kategori
        $rows[] = ['LABA PER KATEGORI', '', ''];
        foreach ($this->data['profitByCategory'] as $item) {
            $rows[] = ['', $item['category_name'], $item['total_profit']];
        }
        $rows[] = ['', '', '']; // Spasi

        // Bagian Rincian per Item
        $rows[] = ['RINCIAN LABA PER ITEM TERJUAL', '', ''];
        $rows[] = ['Tanggal', 'Invoice', 'Produk', 'Qty', 'H. Jual', 'H. Modal', 'Subtotal Laba']; // Sub-header
        
        foreach ($this->data['salesDetails'] as $detail) {
            $purchasePrice = $detail->product?->purchase_price ?? 0;
            $subTotalProfit = ($detail->selling_price_at_transaction - $purchasePrice) * $detail->quantity;
            $rows[] = [
                $detail->transaction->transaction_date->format('Y-m-d'),
                $detail->transaction->invoice_number,
                $detail->product?->name ?: 'Produk Dihapus',
                $detail->quantity,
                $detail->selling_price_at_transaction,
                $purchasePrice,
                $subTotalProfit
            ];
        }

        return $rows;
    }
}