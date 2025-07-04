<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        h1, h3 { text-align: center; margin-bottom: 5px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .sub-header { background-color: #e9ecef; font-weight: bold; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .ps-3 { padding-left: 15px !important; }
    </style>
</head>
<body>
    @include('karung::reports.pdf.partials.header', ['title' => 'Laporan Laba Bersih'])
    
    <h3>Ringkasan Keuangan</h3>
    {{-- [REFACTOR v1.32.1] Tabel Ringkasan Utama dibuat lebih detail --}}
    <table class="table">
        <tbody>
            <!-- Pendapatan -->
            <tr class="sub-header"><td colspan="2">PENDAPATAN</td></tr>
            <tr>
                <td class="ps-3" style="width: 70%;">Pendapatan Kotor (Omzet)</td>
                <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="ps-3">(-) Retur Penjualan</td>
                <td class="text-end text-danger">(-Rp {{ number_format($totalSalesReturns, 0, ',', '.') }})</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td class="fw-bold">PENDAPATAN BERSIH</td>
                <td class="text-end fw-bold">Rp {{ number_format($netRevenue, 0, ',', '.') }}</td>
            </tr>
            
            <!-- HPP -->
            <tr class="sub-header"><td colspan="2">BEBAN POKOK PENJUALAN (HPP)</td></tr>
            <tr>
                <td class="ps-3">HPP dari Penjualan</td>
                <td class="text-end">Rp {{ number_format($totalCostOfGoodsSold, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="ps-3">(-) Pengembalian HPP dari Retur Jual</td>
                <td class="text-end text-danger">(-Rp {{ number_format($costOfReturnedGoods, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td class="ps-3">(-) Nilai Retur Pembelian</td>
                <td class="text-end text-danger">(-Rp {{ number_format($totalPurchaseReturns, 0, ',', '.') }})</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td class="fw-bold">HPP BERSIH</td>
                <td class="text-end fw-bold">Rp {{ number_format($netCostOfGoodsSold, 0, ',', '.') }}</td>
            </tr>

            <!-- Laba Kotor -->
            <tr class="sub-header">
                <td class="fw-bold">LABA KOTOR (PENDAPATAN BERSIH - HPP BERSIH)</td>
                <td class="text-end fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
            </tr>
            
            <!-- Biaya -->
             <tr class="sub-header"><td colspan="2">BIAYA OPERASIONAL</td></tr>
             <tr>
                <td class="ps-3">(-) Total Biaya</td>
                <td class="text-end text-danger">(-Rp {{ number_format($totalExpenses, 0, ',', '.') }})</td>
            </tr>
            
            <!-- Laba Bersih -->
            <tr class="sub-header" style="background-color: {{ $netProfit >= 0 ? '#cfe2ff' : '#f8d7da' }};">
                <td class="fw-bold">LABA BERSIH (LABA KOTOR - BIAYA)</td>
                <td class="text-end fw-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Rincian lain tetap sama --}}
    <h3>Ringkasan Laba per Kategori Produk</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Kategori Produk</th>
                <th class="text-end">Total Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($profitByCategory as $item)
                <tr>
                    <td>{{ $item['category_name'] }}</td>
                    <td class="text-end fw-bold text-success">Rp {{ number_format($item['total_profit'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">Tidak ada data laba per kategori.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Rincian Laba per Item Terjual</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-center">Qty</th>
                <th class="text-end">H. Jual</th>
                <th class="text-end">HPP/item</th>
                <th class="text-end">Subtotal Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesDetails as $detail)
                @php
                    // [FIX] Menggunakan HPP historis yang sudah tercatat
                    $purchasePrice = $detail->purchase_price_at_sale;
                    $subTotalProfit = ($detail->selling_price_at_transaction - $purchasePrice) * $detail->quantity;
                @endphp
                <tr>
                    <td>{{ $detail->product?->name ?: 'Produk Dihapus' }}</td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-end">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($purchasePrice, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold {{ $subTotalProfit < 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($subTotalProfit, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Tidak ada data penjualan pada rentang tanggal yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>