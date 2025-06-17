<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitLossReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $totalSales;
    protected $totalPurchases;
    protected $profitLoss;

    public function __construct($totalSales, $totalPurchases, $profitLoss)
    {
        $this->totalSales = $totalSales;
        $this->totalPurchases = $totalPurchases;
        $this->profitLoss = $profitLoss;
    }

    /**
    * @return array
    */
    public function array(): array
    {
        // Kita hanya membuat satu baris data yang berisi hasil kalkulasi
        return [
            [
                $this->totalSales,
                $this->totalPurchases,
                $this->profitLoss,
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Total Penjualan',
            'Total Pembelian (Modal)',
            'Laba Rugi Kotor',
        ];
    }
}