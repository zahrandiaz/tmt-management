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
        .sub-header { background-color: #f9f9f9; font-weight: bold; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Laporan Laba Bersih</h1>
    <p style="text-align:center; margin-top:0;">Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}</p>
    
    <h3>Ringkasan Keuangan</h3>
    <table class="table">
        <tr><th style="width: 70%;">Total Pendapatan (Omzet)</th><td class="text-end fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td></tr>
        <tr><th>(-) Total Modal Terjual (HPP)</th><td class="text-end fw-bold">Rp {{ number_format($totalCost, 0, ',', '.') }}</td></tr>
        <tr class="sub-header"><th>(=) Laba Kotor</th><th class="text-end">Rp {{ number_format($grossProfit, 0, ',', '.') }}</th></tr>
        <tr><th>(-) Total Biaya Operasional</th><td class="text-end fw-bold">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td></tr>
        <tr class="sub-header"><th>(=) Laba Bersih</th><th class="text-end">Rp {{ number_format($netProfit, 0, ',', '.') }}</th></tr>
    </table>

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

    <h3>Rincian Biaya Operasional</h3>
    <table class="table">
        <thead>
            <tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th class="text-end">Jumlah</th></tr>
        </thead>
        <tbody>
            @forelse ($expensesDetails as $expense)
                <tr>
                    <td>{{ $expense->date->format('d-m-Y') }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ $expense->description }}</td>
                    <td class="text-end">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Tidak ada data biaya operasional.</td></tr>
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
                <th class="text-end">H. Modal</th>
                <th class="text-end">Subtotal Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesDetails as $detail)
                @php
                    $purchasePrice = $detail->product?->purchase_price ?? 0;
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