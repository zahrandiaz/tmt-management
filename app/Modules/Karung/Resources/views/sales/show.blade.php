@extends('layouts.tmt_app')

@section('title', 'Detail Transaksi Penjualan - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Transaksi Penjualan: #{{ $sale->invoice_number }}</h5>
                    <div>
                        {{-- <a href="#" class="btn btn-light btn-sm">Cetak Struk</a> --}}
                        <a href="{{ route('karung.sales.index') }}" class="btn btn-outline-light btn-sm">
                            &larr; Kembali ke Daftar Penjualan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Informasi Utama Transaksi --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>No. Invoice:</strong> {{ $sale->invoice_number }}</p>
                            <p><strong>Tanggal Transaksi:</strong> {{ $sale->transaction_date->format('d F Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Pelanggan:</strong> {{ $sale->customer?->name ?: 'Penjualan Umum' }}</p>
                            <p><strong>Dicatat Oleh:</strong> {{ $sale->user?->name ?: 'N/A' }}</p>
                        </div>
                         @if($sale->notes)
                        <div class="col-12">
                            <p><strong>Catatan:</strong> {{ $sale->notes }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Tabel Detail Produk --}}
                    <h5 class="mb-3">Rincian Produk yang Dijual</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 5%;">No.</th>
                                    <th scope="col">Nama Produk</th>
                                    <th scope="col" class="text-center">Jumlah</th>
                                    <th scope="col" class="text-end">Harga Jual Satuan</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale->details as $index => $detail)
                                    <tr>
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td>{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="4" class="text-end">TOTAL PENJUALAN</th>
                                    <th class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection