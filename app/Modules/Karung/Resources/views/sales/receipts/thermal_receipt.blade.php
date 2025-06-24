<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #000;
            width: 58mm;
            margin: 0;
            padding: 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h4 {
            margin: 0;
            font-size: 12pt;
        }
        .header p {
            margin: 0;
            font-size: 9pt;
        }
        .transaction-details, .items-table, .summary {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            padding: 2px 0;
        }
        .items-table .price-col {
            width: 70px; /* Lebar kolom harga */
        }
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            margin-top: 10px;
        }
        /* Sembunyikan header dan footer default browser saat print */
        @page {
            size: 58mm;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <h4>{{ $settings['store_name'] ?? 'Toko Anda' }}</h4>
        <p>{{ $settings['store_address'] ?? '' }}</p>
        <p>{{ $settings['store_phone'] ?? '' }}</p>
    </div>

    <div class="line"></div>

    <div class="transaction-details">
        <table>
            <tr>
                <td>No:</td>
                <td class="text-right">{{ $sale->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal:</td>
                <td class="text-right">{{ $sale->transaction_date->format('d/m/y H:i') }}</td>
            </tr>
            <tr>
                <td>Kasir:</td>
                <td class="text-right">{{ $sale->user->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="line"></div>

    <div class="items-table">
        <table>
            <tbody>
                @foreach ($sale->details as $detail)
                <tr>
                    <td colspan="2">{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                </tr>
                <tr>
                    <td>{{ $detail->quantity }} x {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                    <td class="text-right price-col">{{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="line"></div>

    <div class="summary">
        <table>
            <tr>
                <td>TOTAL</td>
                <td class="text-right"><b>{{ number_format($sale->total_amount, 0, ',', '.') }}</b></td>
            </tr>
            <tr>
                <td>BAYAR</td>
                <td class="text-right">{{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>KEMBALI</td>
                <td class="text-right">{{ number_format($sale->amount_paid - $sale->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
    @if(isset($qrCode))
    <div class="text-center" style="margin-top: 10px;">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code Verifikasi">
        <p style="font-size: 8pt; margin: 5px 0 0 0;">Scan untuk verifikasi</p>
    </div>
    @endif

    {{-- [BARU v1.28] Tampilkan kode verifikasi manual --}}
    @if($sale->verification_code)
        <div class="text-center" style="margin-top: 5px;">
            <p style="font-size: 8pt; margin: 0;">atau cek manual di web kami</p>
            <p style="font-size: 10pt; font-weight: bold; margin: 0; letter-spacing: 1px;">
                Kode: {{ $sale->verification_code }}
            </p>
        </div>
    @endif

    <p style="margin-top: 10px;">Terima kasih telah berbelanja!</p>
    </div>

    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>