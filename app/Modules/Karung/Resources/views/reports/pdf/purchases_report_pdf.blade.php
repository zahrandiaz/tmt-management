<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        h1 { text-align: center; margin-bottom: 20px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Pembelian</h1>
    <p>Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}</p>
    <p>Total Pembelian: <strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong></p>
    <hr>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Pembelian</th>
                <th>No. Referensi</th>
                <th>Supplier</th>
                <th>Dicatat Oleh</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->transaction_date->format('d-m-Y H:i') }}</td>
                    <td>{{ $purchase->purchase_code }}</td>
                    <td>{{ $purchase->purchase_reference_no ?: '-' }}</td>
                    <td>{{ $purchase->supplier->name ?? 'Pembelian Umum' }}</td>
                    <td>{{ $purchase->user->name ?? 'N/A' }}</td>
                    <td class="text-end">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>