@extends('karung::layouts.karung_app')

@section('title', 'Detail Retur Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Retur: #{{ $salesReturn->return_code }}</h5>
                    <a href="{{ route('karung.returns.sales.index') }}" class="btn btn-light btn-sm">&larr; Kembali ke Riwayat Retur</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6"><p><strong>Kode Retur:</strong> {{ $salesReturn->return_code }}</p><p><strong>Tanggal Retur:</strong> {{ $salesReturn->return_date->format('d F Y') }}</p><p><strong>Invoice Asli:</strong> <a href="{{ route('karung.sales.show', $salesReturn->originalTransaction->id) }}">{{ $salesReturn->originalTransaction->invoice_number }}</a></p></div>
                        <div class="col-md-6"><p><strong>Pelanggan:</strong> {{ $salesReturn->customer->name }}</p><p><strong>Dicatat Oleh:</strong> {{ $salesReturn->user->name }}</p><p><strong>Alasan:</strong> {{ $salesReturn->reason ?: '-' }}</p></div>
                    </div>
                    <h5 class="mb-3">Rincian Produk yang Diretur</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light"><tr><th>No.</th><th>Nama Produk</th><th class="text-center">Jumlah</th><th class="text-end">Harga Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody>
                                @foreach($salesReturn->details as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->product->name }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr class="table-dark"><th colspan="4" class="text-end">TOTAL NILAI RETUR</th><th class="text-end">Rp {{ number_format($salesReturn->total_amount, 0, ',', '.') }}</th></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection