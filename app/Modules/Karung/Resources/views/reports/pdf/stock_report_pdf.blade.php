<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Produk</title>
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
    <h1>Laporan Stok Produk</h1>
    <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</p>
    <hr>
    <table class="table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Jenis</th>
                <th class="text-center">Stok Saat Ini</th>
                <th class="text-end">Harga Beli</th>
                <th class="text-end">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->type->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $product->stock }}</td>
                    <td class="text-end">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data produk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>