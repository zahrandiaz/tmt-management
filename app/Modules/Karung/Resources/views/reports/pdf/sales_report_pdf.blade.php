<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <p>Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}</p>
    <p>Total Omzet: <strong>Rp {{ number_format($totalOmzet, 0, ',', '.') }}</strong></p>
    <hr>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Pelanggan</th>
                <th>Dicatat Oleh</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->customer->name ?? 'Penjualan Umum' }}</td>
                    <td>{{ $sale->user->name ?? 'N/A' }}</td>
                    <td class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>