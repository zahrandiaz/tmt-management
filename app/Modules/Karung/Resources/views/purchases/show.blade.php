@extends('layouts.tmt_app')

@section('title', 'Detail Transaksi Pembelian - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Transaksi Pembelian</h5>
                    <a href="{{ route('karung.purchases.index') }}" class="btn btn-light btn-sm">
                        &larr; Kembali ke Daftar Pembelian
                    </a>
                </div>
                <div class="card-body">
                    {{-- Informasi Utama Transaksi --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Tanggal Transaksi:</strong> {{ $purchase->transaction_date->format('d F Y') }}</p>
                            <p><strong>Supplier:</strong> {{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>No. Referensi:</strong> {{ $purchase->purchase_reference_no ?: '-' }}</p>
                            <p><strong>Dicatat Oleh:</strong> {{ $purchase->user?->name ?: 'N/A' }}</p>
                        </div>
                         @if($purchase->notes)
                        <div class="col-12">
                            <p><strong>Catatan:</strong> {{ $purchase->notes }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Tabel Detail Produk --}}
                    <h5 class="mb-3">Rincian Produk yang Dibeli</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 5%;">No.</th>
                                    <th scope="col">Nama Produk</th>
                                    <th scope="col" class="text-center">Jumlah</th>
                                    <th scope="col" class="text-end">Harga Beli Satuan</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->details as $index => $detail)
                                    <tr>
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td>{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->purchase_price_at_transaction, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="4" class="text-end">TOTAL PEMBELIAN</th>
                                    <th class="text-end">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Tampilan Attachment/Struk --}}
                    @if($purchase->attachment_path)
                    <div class="mt-4">
                        <h5>Lampiran Struk/Nota:</h5>
                        <a href="{{ asset('storage/' . $purchase->attachment_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $purchase->attachment_path) }}" alt="Lampiran Pembelian" class="img-thumbnail" style="max-width: 300px;">
                        </a>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection