<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Detail</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        h1, h2 { text-align: center; margin-bottom: 5px; }
        .summary-table { width: 50%; margin-bottom: 20px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .sub-table { width: 100%; margin-top: 5px; border-collapse: collapse; }
        .sub-table th, .sub-table td { border: 1px solid #e3e3e3; padding: 4px; }
        .sub-table th { background-color: #fafafa; }
        .master-row { background-color: #f9f9f9; font-weight: bold; }
        .page-break { page-break-after: always; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan Detail</h1>
    <p style="text-align:center; margin-top:0;">Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}</p>
    
    <table class="summary-table">
        <tr>
            <td>Total Pendapatan</td>
            <td class="text-end"><strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Total Modal (HPP)</td>
            <td class="text-end">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th style="background-color:#f2f2f2;">Total Laba Kotor</th>
            <th class="text-end fw-bold" style="background-color:#f2f2f2;">Rp {{ number_format($grossProfit, 0, ',', '.') }}</th>
        </tr>
    </table>

    <hr>

    <table class="table">
        <thead class="table-dark">
            <tr>
                <th style="width: 1%;">No.</th>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th class="text-end">Total Modal</th>
                <th class="text-end">Total Laba</th>
                <th class="text-end">Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                @php
                    $totalModal = $sale->details->reduce(fn($c, $d) => $c + (($d->product->purchase_price ?? 0) * $d->quantity), 0);
                    $totalLaba = $sale->total_amount - $totalModal;
                @endphp
                <tr class="master-row">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                    <td>{{ $sale->customer->name ?? 'Penjualan Umum' }}</td>
                    <td class="text-end">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold {{ $totalLaba >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalLaba, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border: none;"></td>
                    <td colspan="6" style="padding: 5px 5px 15px 5px; border: none;">
                        <table class="sub-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Modal/Pcs</th>
                                    <th class="text-end">Harga Jual/Pcs</th>
                                    <th class="text-end">Subtotal Laba</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->details as $detail)
                                @php
                                    $modalPerPcs = $detail->product->purchase_price ?? 0;
                                    $subLaba = ($detail->selling_price_at_transaction - $modalPerPcs) * $detail->quantity;
                                @endphp
                                <tr>
                                    <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($modalPerPcs, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold {{ $subLaba >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($subLaba, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Tidak ada data untuk periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>