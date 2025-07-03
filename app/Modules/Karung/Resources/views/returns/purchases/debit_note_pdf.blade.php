<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Debit - {{ $purchaseReturn->return_code }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; }
        .content { margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-end { text-align: right; }
        .total-section { margin-top: 20px; float: right; width: 40%; }
        .total-section .table th, .total-section .table td { border: none; padding: 5px 8px; }
        .total-section .table th { text-align: left; }
        .total-section .table td { text-align: right; font-weight: bold; }
        .notes-section { margin-top: 40px; clear: both; }
        .footer { margin-top: 50px; font-size: 12px; color: #777; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($settings['shop_logo_path']))
                {{-- Logika untuk menampilkan logo jika ada --}}
            @endif
            <h1>NOTA DEBIT</h1>
            <p><strong>{{ $settings['shop_name'] ?? 'TMT Management' }}</strong></p>
            <p>{{ $settings['shop_address'] ?? '' }}</p>
            <p>Telepon: {{ $settings['shop_phone'] ?? '' }}</p>
        </div>

        <div class="content">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%;">
                        <strong>Kepada Yth:</strong><br>
                        {{ $purchaseReturn->supplier->name ?? 'N/A' }}<br>
                        {{ $purchaseReturn->supplier->address ?? '' }}<br>
                        {{ $purchaseReturn->supplier->phone ?? '' }}
                    </td>
                    <td style="width: 50%;" class="text-end">
                        <strong>No. Nota Debit:</strong> {{ $purchaseReturn->return_code }}<br>
                        <strong>Tanggal:</strong> {{ $purchaseReturn->return_date->format('d F Y') }}<br>
                        <strong>Ref. Pembelian:</strong> {{ $purchaseReturn->originalTransaction->purchase_code ?? 'N/A' }}
                    </td>
                </tr>
            </table>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th>Deskripsi Produk</th>
                        <th class="text-end">Kuantitas</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseReturn->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->product->name ?? 'Produk Telah Dihapus' }}</td>
                        <td class="text-end">{{ $detail->quantity }}</td>
                        <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <table class="table">
                    <tr>
                        <th>TOTAL NILAI RETUR</th>
                        <td>Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <div class="notes-section clearfix">
                <strong>Alasan Retur:</strong>
                <p>{{ $purchaseReturn->reason ?? '-' }}</p>
            </div>
        </div>

        <div class="footer">
            <p>Dokumen ini dicetak oleh sistem TMT Management dan sah tanpa tanda tangan.</p>
        </div>
    </div>
</body>
</html>