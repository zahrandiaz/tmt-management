@extends('layouts.tmt_app')

@section('title', 'Detail Transaksi Pembelian - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Transaksi Pembelian</h5>
                    <div>
                         @if($purchase->status == 'Completed')
                        <form action="{{ route('karung.purchases.cancel', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Aksi ini tidak dapat diurungkan.');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.647a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                </svg>
                                Batalkan Transaksi
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('karung.purchases.index') }}" class="btn btn-light btn-sm">
                            &larr; Kembali ke Daftar Pembelian
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Alert jika transaksi dibatalkan --}}
                    @if($purchase->status == 'Cancelled')
                        <div class="alert alert-danger">
                            <strong>Transaksi Dibatalkan!</strong> Transaksi ini telah dibatalkan dan tidak lagi dihitung dalam laporan.
                        </div>
                    @endif

                    {{-- Informasi Utama Transaksi --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Tanggal Transaksi:</strong> {{ $purchase->transaction_date->format('d F Y') }}</p>
                            <p><strong>Supplier:</strong> {{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</p>
                            <p><strong>Status:</strong> 
                                @if($purchase->status == 'Cancelled')
                                    <span class="badge bg-danger">{{ $purchase->status }}</span>
                                @else
                                    <span class="badge bg-success">{{ $purchase->status }}</span>
                                @endif
                            </p>
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
                                    {{-- Beri style berbeda jika transaksi dibatalkan --}}
                                    <tr class="{{ $purchase->status == 'Cancelled' ? 'text-muted' : '' }}">
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td class="{{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                        <td class="text-center {{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td>
                                        <td class="text-end {{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->purchase_price_at_transaction, 0, ',', '.') }}</td>
                                        <td class="text-end {{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="4" class="text-end">TOTAL PEMBELIAN</th>
                                    <th class="text-end {{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
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