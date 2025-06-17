<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        h1 { text-align: center; margin-bottom: 5px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .period { text-align: center; margin-bottom: 20px; color: #555; }
        .summary-value { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Laba Rugi Kotor</h1>
    <p class="period">
        Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}
    </p>

    <table class="table">
        <tbody>
            <tr>
                <th style="width: 70%;">Total Pendapatan (Penjualan)</th>
                <td class="text-end summary-value">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total Biaya (Pembelian)</th>
                <td class="text-end summary-value">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th>LABA RUGI KOTOR</th>
                <td class="text-end summary-value">Rp {{ number_format($profitLoss, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 9px; color: #777;">
        *Laporan ini hanya menghitung laba rugi kotor berdasarkan total penjualan dikurangi total pembelian pada periode yang dipilih. Biaya operasional lain tidak termasuk.
    </p>
</body>
</html>