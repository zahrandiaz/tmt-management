<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Struk - {{ $transaction->invoice_number }}</title>
    {{-- Kita gunakan CDN Bootstrap untuk styling halaman publik ini --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .receipt-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .verified-badge {
            font-size: 1.2rem;
            font-weight: 600;
            color: #198754;
        }
        .verified-badge svg {
            vertical-align: middle;
        }
        .receipt-header h4, .receipt-header p {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt-container">
            <div class="text-center mb-4">
                <h3 class="verified-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-patch-check-fill me-2" viewBox="0 0 16 16">
                        <path d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01zM6.854 6.854a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L8.293 9.5 6.146 7.354a.5.5 0 0 1 0-.708"/>
                    </svg>
                    Struk Terverifikasi
                </h3>
            </div>

            <div class="receipt-header text-center mb-4">
                {{-- TODO: Ganti dengan info toko dari database Settings --}}
                <h4>Toko Karung TMT</h4> 
                <p class="text-muted small">Jl. Raya Kodep, No. 123, Cibinong, Bogor</p>
            </div>
            <hr>
            
            <div class="row mb-3">
                <div class="col-6">
                    <strong>No. Invoice:</strong><br>
                    {{ $transaction->invoice_number }}
                </div>
                <div class="col-6 text-end">
                    <strong>Tanggal:</strong><br>
                    {{ $transaction->transaction_date->format('d M Y, H:i') }}
                </div>
                <div class="col-12 mt-2">
                    <strong>Pelanggan:</strong><br>
                    {{ $transaction->customer->name }}
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-end">Rp{{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                        <td class="text-end">Rp{{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end border-top">TOTAL</td>
                        <td class="text-end border-top">Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
            
            <p class="text-center text-muted small mt-4">Ini adalah struk digital yang telah diverifikasi oleh sistem TMT Management.</p>
        </div>
    </div>
</body>
</html>