<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .invoice-details { margin-bottom: 20px; }
        .invoice-details table { width: 100%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; }
        .items-table th { background-color: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
        .summary-table { width: 50%; float: right; }
        .summary-table td { padding: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $settings['store_name'] ?? 'Toko Anda' }}</h1>
            <p>{{ $settings['store_address'] ?? '' }}<br>
            Telp: {{ $settings['store_phone'] ?? '' }}</p>
        </div>

        <hr>

        <h2>INVOICE</h2>

        <div class="invoice-details">
            <table>
                <tr>
                    <td><strong>No. Invoice:</strong> {{ $sale->invoice_number }}</td>
                    <td class="text-right"><strong>Tanggal:</strong> {{ $sale->transaction_date->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Pelanggan:</strong> {{ $sale->customer->name ?? 'Pelanggan Umum' }}</td>
                    <td class="text-right"><strong>Kasir:</strong> {{ $sale->user->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Produk</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                        <td class="text-right">{{ $detail->quantity }}</td>
                        <td class="text-right">{{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Pembayaran</td>
                <td class="text-right">Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
            </tr>
             <tr>
                <td>Sisa</td>
                <td class="text-right">Rp {{ number_format($sale->amount_paid - $sale->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div style="clear: both;"></div>
        {{-- Tambahkan ini di file pdf_receipt.blade.php Anda --}}
        @if(isset($qrCode))
        <div style="text-align: center; margin-top: 20px;">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code Verifikasi">
            <p style="font-size: 9pt;">Scan untuk verifikasi struk</p>
        </div>
        @endif
        <div class="footer" style="margin-top: 50px;">
            <p>Terima kasih telah berbelanja!</p>
        </div>
    </div>
</body>
</html>