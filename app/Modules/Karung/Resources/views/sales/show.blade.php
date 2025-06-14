@extends('karung::layouts.karung_app')

@section('title', 'Detail Transaksi Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Transaksi Penjualan: #{{ $sale->invoice_number }}</h5>
                    <div>
                        <button type="button" onclick="window.print()" class="btn btn-light btn-sm no-print">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill me-1" viewBox="0 0 16 16">
                                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-1 2a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1z"/>
                            </svg> Cetak Struk
                        </button>
                        
                        {{-- [MODIFIKASI] Tombol Edit dan Batal hanya muncul untuk status Completed --}}
                        @if($sale->status == 'Completed')
                            @can('karung.edit_sales')
                                <a href="{{ route('karung.sales.edit', $sale->id) }}" class="btn btn-warning btn-sm no-print" title="Edit Transaksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                    </svg> Edit
                                </a>
                            @endcan
                            @can('karung.cancel_sales')
                            <form action="{{ route('karung.sales.cancel', $sale->id) }}" method="POST" class="d-inline no-print" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Aksi ini tidak dapat diurungkan.');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.647a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                    </svg> Batalkan Transaksi
                                </button>
                            </form>
                            @endcan
                        @endif

                        <a href="{{ route('karung.sales.index') }}" class="btn btn-outline-light btn-sm no-print">
                            &larr; Kembali ke Daftar Penjualan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- ... (Sisa kode view tidak berubah) ... --}}
                    @if($sale->status == 'Cancelled')
                        <div class="alert alert-danger">
                            <strong>Transaksi Dibatalkan!</strong> Transaksi ini telah dibatalkan dan tidak lagi dihitung dalam laporan.
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>No. Invoice:</strong> {{ $sale->invoice_number }}</p>
                            <p><strong>Tanggal Transaksi:</strong> {{ $sale->transaction_date->format('d F Y, H:i') }}</p>
                            <p><strong>Status:</strong> 
                                @if($sale->status == 'Cancelled')
                                    <span class="badge bg-danger">{{ $sale->status }}</span>
                                @else
                                    <span class="badge bg-success">{{ $sale->status }}</span>
                                @endif
                            </p>
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
                                    <tr class="{{ $sale->status == 'Cancelled' ? 'text-muted' : '' }}">
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td class="{{ $sale->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                        <td class="text-center {{ $sale->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td>
                                        <td class="text-end {{ $sale->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                        <td class="text-end {{ $sale->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="4" class="text-end">TOTAL PENJUALAN</th>
                                    <th class="text-end {{ $sale->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th>
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
