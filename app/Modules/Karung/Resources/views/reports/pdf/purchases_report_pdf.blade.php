<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian Detail</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top;}
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
    </style>
</head>
<body>
    <h1>Laporan Pembelian Detail</h1>
    <p style="text-align:center; margin-top:0;">Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}</p>
    
    <table class="summary-table">
        <tr>
            <th style="background-color:#f2f2f2;">Total Pengeluaran Pembelian</th>
            <th class="text-end" style="background-color:#f2f2f2;">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</th>
        </tr>
    </table>

    <hr>

    <table class="table">
        <thead class="table-dark">
            <tr>
                <th style="width: 1%;">No.</th>
                <th>Kode Pembelian</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th class="text-end">Total Pembelian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr class="master-row">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $purchase->purchase_code }}</td>
                    <td>{{ $purchase->transaction_date->format('d-m-Y H:i') }}</td>
                    <td>{{ $purchase->supplier->name ?? 'Pembelian Umum' }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="border: none;"></td>
                    <td colspan="4" style="padding: 5px 5px 15px 5px; border: none;">
                        <strong>Rincian Produk:</strong>
                        <table class="sub-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Beli/Pcs</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->details as $detail)
                                <tr>
                                    <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->purchase_price_at_transaction, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Tidak ada data untuk periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>