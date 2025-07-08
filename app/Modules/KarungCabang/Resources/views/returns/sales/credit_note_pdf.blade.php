<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Kredit - {{ $salesReturn->return_code }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; font-size: 24px; color: #555; }
        .header p { margin: 2px 0; }
        .content { margin-top: 30px; }
        .details-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .details-box table { width: 100%; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; float: right; width: 40%; }
        .total-section table { width: 100%; }
        .total-section td { padding: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NOTA KREDIT</h1>
            <p><strong>{{ $settings['store_name'] ?? 'Toko Anda' }}</strong></p>
            <p>{{ $settings['store_address'] ?? 'Alamat Toko' }}</p>
            <p>Telp: {{ $settings['store_phone'] ?? 'Telepon Toko' }}</p>
        </div>

        <div class="content">
            <div class="details-box">
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <strong>Kepada:</strong><br>
                            {{ $salesReturn->customer->name }}<br>
                            {{ $salesReturn->customer->address ?? '' }}<br>
                            {{ $salesReturn->customer->phone_number ?? '' }}
                        </td>
                        <td style="width: 50%;" class="text-right">
                            <strong>No. Nota Kredit:</strong> {{ $salesReturn->return_code }}<br>
                            <strong>Tanggal:</strong> {{ $salesReturn->return_date->format('d F Y') }}<br>
                            <strong>Ref. Invoice Asli:</strong> {{ $salesReturn->originalTransaction->invoice_number }}
                        </td>
                    </tr>
                </table>
            </div>

            <p>Kami telah mengkreditkan akun Anda dengan rincian sebagai berikut:</p>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Deskripsi Produk</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesReturn->details as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        <td class="text-right">{{ $detail->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <table>
                    <tr>
                        <td><strong>Total Kredit:</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($salesReturn->total_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="footer" style="position: fixed; bottom: 50px; width: 100%;">
            <p>Terima kasih atas kerja sama Anda.</p>
        </div>
    </div>
</body>
</html>