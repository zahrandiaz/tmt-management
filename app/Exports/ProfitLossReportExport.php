<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossReportExport implements FromArray, ShouldAutoSize, WithTitle, WithStyles
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

    public function styles(Worksheet $sheet)
    {
        // Memberi style bold pada baris-baris penting
        return [
            1  => ['font' => ['bold' => true]],
            2  => ['font' => ['bold' => true]],
            5  => ['font' => ['bold' => true]],
            7  => ['font' => ['bold' => true]],
            11 => ['font' => ['bold' => true]],
            13 => ['font' => ['bold' => true]],
            15 => ['font' => ['bold' => true]],
            17 => ['font' => ['bold' => true]],
            20 => ['font' => ['bold' => true]],
        ];
    }

    /**
    * @return array
    */
    public function array(): array
    {
        $rows = [];

        // [REFACTOR v1.32.1] Bagian Ringkasan Utama dibuat lebih detail
        $rows[] = ['RINGKASAN LABA RUGI', ''];
        $rows[] = ['PENDAPATAN', ''];
        $rows[] = ['   Pendapatan Kotor (Omzet)', $this->data['totalRevenue']];
        $rows[] = ['   (-) Retur Penjualan', $this->data['totalSalesReturns']];
        $rows[] = ['PENDAPATAN BERSIH', $this->data['netRevenue']];
        $rows[] = ['']; // Spasi

        $rows[] = ['BEBAN POKOK PENJUALAN (HPP)', ''];
        $rows[] = ['   HPP dari Penjualan', $this->data['totalCostOfGoodsSold']];
        $rows[] = ['   (-) Pengembalian HPP dari Retur Jual', $this->data['costOfReturnedGoods']];
        $rows[] = ['   (-) Nilai Retur Pembelian', $this->data['totalPurchaseReturns']];
        $rows[] = ['HPP BERSIH', $this->data['netCostOfGoodsSold']];
        $rows[] = ['']; // Spasi

        $rows[] = ['LABA KOTOR', $this->data['grossProfit']];
        $rows[] = ['']; // Spasi

        $rows[] = ['BIAYA OPERASIONAL', ''];
        $rows[] = ['   Total Biaya', $this->data['totalExpenses']];
        $rows[] = ['LABA BERSIH', $this->data['netProfit']];
        $rows[] = ['']; // Spasi
        $rows[] = ['']; // Spasi
        
        // Bagian Laba per Kategori
        $rows[] = ['LABA PER KATEGORI', ''];
        foreach ($this->data['profitByCategory'] as $item) {
            $rows[] = [$item['category_name'], $item['total_profit']];
        }
        $rows[] = ['']; // Spasi

        // Bagian Rincian per Item
        $rows[] = ['RINCIAN LABA PER ITEM TERJUAL', '', '', '', '', '', ''];
        $rows[] = ['Tanggal', 'Invoice', 'Produk', 'Qty', 'H. Jual', 'HPP/item', 'Subtotal Laba'];
        
        foreach ($this->data['salesDetails'] as $detail) {
            // [FIX] Menggunakan HPP historis yang sudah tercatat di detail
            $purchasePrice = $detail->purchase_price_at_sale;
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