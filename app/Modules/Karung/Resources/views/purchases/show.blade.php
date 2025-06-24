@extends('karung::layouts.karung_app')

@section('title', 'Detail Transaksi Pembelian - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Transaksi Pembelian</h5>
                    <div class="no-print">
                        {{-- [BARU v1.28] Tombol Buat Retur Pembelian --}}
                        @can('karung.manage_returns')
                            @if($purchase->status == 'Completed')
                                <a href="{{ route('karung.purchases.returns.create', $purchase->id) }}" class="btn btn-warning btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-left me-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0z"/><path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708z"/></svg>
                                    Buat Retur
                                </a>
                            @endif
                        @endcan

                        @if($purchase->status == 'Completed')
                            @can('karung.edit_purchases')
                                <a href="{{ route('karung.purchases.edit', $purchase->id) }}" class="btn btn-warning btn-sm" title="Edit Transaksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Edit
                                </a>
                            @endcan
                            @can('karung.cancel_purchases')
                                <form action="{{ route('karung.purchases.cancel', $purchase->id) }}" method="POST" class="d-inline cancel-form">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.647a.5.5 0 0 0-.708-.708L8 7.293z"/></svg> Batalkan</button>
                                </form>
                            @endcan
                            @can('karung.delete_purchases')
                                <form action="{{ route('karung.purchases.destroy', $purchase->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-dark btn-sm" title="Hapus Transaksi">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.024l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m3.5-.05l-.5 8.5a.5.5 0 1 0 .998.06l.5-8.5a.5.5 0 1 0-.998-.06m3.5.002l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998-.06"/></svg> Hapus
                                    </button>
                                </form>
                            @endcan
                        @endif
                        <a href="{{ route('karung.purchases.index') }}" class="btn btn-light btn-sm">
                            &larr; Kembali ke Daftar Pembelian
                        </a>
                    </div>
                    
                </div>
                <div class="card-body">
                    @if($purchase->status == 'Cancelled' || $purchase->status == 'Deleted')
                        <div class="alert alert-danger">
                            <strong>Transaksi {{ $purchase->status }}!</strong> Transaksi ini telah ditandai sebagai '{{ $purchase->status }}' dan tidak lagi dihitung dalam laporan.
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6"><p class="mb-1"><strong>Kode Pembelian:</strong> <span class="badge bg-dark fs-6">{{ $purchase->purchase_code }}</span></p><p class="mb-1"><strong>Tanggal Transaksi:</strong> {{ $purchase->transaction_date->format('d F Y') }}</p><p class="mb-1"><strong>Supplier:</strong> {{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</p></div>
                        <div class="col-md-6"><p class="mb-1"><strong>No. Referensi:</strong> {{ $purchase->purchase_reference_no ?: '-' }}</p><p class="mb-1"><strong>Dicatat Oleh:</strong> {{ $purchase->user?->name ?: 'N/A' }}</p><p class="mb-1"><strong>Status:</strong> @if($purchase->status == 'Completed')<span class="badge bg-success">{{ $purchase->status }}</span>@else<span class="badge bg-danger">{{ $purchase->status }}</span>@endif</p></div>
                        @if($purchase->notes)<div class="col-12 mt-2"><p class="mb-1"><strong>Catatan:</strong> {{ $purchase->notes }}</p></div>@endif
                    </div>

                    {{-- [MODIFIKASI] Detail Pembayaran --}}
                    <div class="row pt-3 mt-3 border-top">
                        <h5 class="mb-3">Informasi Pembayaran</h5>
                        <div class="col-md-4"><p class="mb-1"><strong>Status Pembayaran:</strong> 
                            @if($purchase->payment_status == 'Lunas')
                                <span class="badge bg-primary">Lunas</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Lunas</span>
                            @endif
                        </p></div>
                        <div class="col-md-4"><p class="mb-1"><strong>Metode Pembayaran:</strong> {{ $purchase->payment_method }}</p></div>
                        <div class="col-md-4"><p class="mb-1"><strong>Jumlah Dibayar:</strong> Rp {{ number_format($purchase->amount_paid, 0, ',', '.') }}</p></div>
                        @if($purchase->payment_status == 'Belum Lunas')
                            <div class="col-12 mt-2"><p class="mb-1 fw-bold text-danger"><strong>Sisa Tagihan:</strong> Rp {{ number_format($purchase->total_amount - $purchase->amount_paid, 0, ',', '.') }}</p></div>
                        @endif
                    </div>
                    <hr class="mb-4">

                    <h5 class="mb-3">Rincian Produk yang Dibeli</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light"><tr><th scope="col" style="width: 5%;">No.</th><th scope="col">Nama Produk</th><th scope="col" class="text-center">Jumlah</th><th scope="col" class="text-end">Harga Beli Satuan</th><th scope="col" class="text-end">Subtotal</th></tr></thead>
                            <tbody>@foreach ($purchase->details as $index => $detail)<tr class="{{ $purchase->status != 'Completed' ? 'text-muted' : '' }}"><th scope="row">{{ $index + 1 }}</th><td class="{{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td><td class="text-center {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td><td class="text-end {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->purchase_price_at_transaction, 0, ',', '.') }}</td><td class="text-end {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td></tr>@endforeach</tbody>
                            <tfoot><tr class="table-dark"><th colspan="4" class="text-end">TOTAL PEMBELIAN</th><th class="text-end {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</th></tr></tfoot>
                        </table>
                    </div>

                    @if($purchase->attachment_path)
                    <div class="mt-4"><h5>Lampiran Struk/Nota:</h5><a href="{{ asset('storage/' . $purchase->attachment_path) }}" target="_blank"><img src="{{ asset('storage/' . $purchase->attachment_path) }}" alt="Lampiran Pembelian" class="img-thumbnail" style="max-width: 300px;"></a></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Script SweetAlert2 untuk halaman ini --}}
@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Karena hanya ada satu form per halaman, kita bisa gunakan ID atau class
        const deleteForm = document.querySelector('.delete-form');
        if(deleteForm) {
            deleteForm.addEventListener('submit', function (event) {
                event.preventDefault();
                Swal.fire({
                    title: 'PERINGATAN!',
                    text: "Menghapus transaksi akan menyembunyikannya dari daftar. Anda yakin?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteForm.submit();
                    }
                });
            });
        }

        const cancelForm = document.querySelector('.cancel-form');
        if(cancelForm) {
            cancelForm.addEventListener('submit', function (event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Transaksi ini akan dibatalkan. Aksi ini tidak dapat diurungkan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        cancelForm.submit();
                    }
                });
            });
        }
    });
</script>
@endpush