@extends('karung::layouts.karung_app')

@section('title', 'Detail Transaksi Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Transaksi Penjualan: #{{ $sale->invoice_number }}</h5>
                    <div class="no-print">
                        <a href="{{ route('karung.sales.print.thermal', $sale) }}" target="_blank" class="btn btn-light btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill me-1" viewBox="0 0 16 16"><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-1 2a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1z"/></svg>
                            Cetak Struk (58mm)
                        </a>
                        <a href="{{ route('karung.sales.download.pdf', $sale) }}" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf-fill me-1" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572a.27.27 0 0 1-.035.012.28.28 0 0 1-.031-.023c-.075-.041-.158-.1-.218-.17a.85.85 0 0 1-.135-.37c-.014-.042-.027-.102-.038-.172a.21.21 0 0 1 .035-.145c.022-.02.05-.038.083-.051a.2.2 0 0 1 .051-.028.2.2 0 0 1 .068.004q.032.007.07.02z"/><path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM.5 11.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/></svg>
                            Download PDF
                        </a>

                        @if($sale->status == 'Completed')
                            {{-- Tombol Edit, Batalkan, Hapus tetap ada di sini --}}
                        @endif

                        <a href="{{ route('karung.sales.index') }}" class="btn btn-outline-light btn-sm">
                            &larr; Kembali ke Daftar Penjualan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($sale->status != 'Completed')
                        <div class="alert alert-danger"><strong>Transaksi {{ $sale->status }}!</strong> Transaksi ini telah ditandai sebagai '{{ $sale->status }}' dan tidak lagi dihitung dalam laporan.</div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-md-6"><p><strong>No. Invoice:</strong> {{ $sale->invoice_number }}</p><p><strong>Tanggal Transaksi:</strong> {{ $sale->transaction_date->format('d F Y, H:i') }}</p><p><strong>Status:</strong> @if($sale->status == 'Completed')<span class="badge bg-success">{{ $sale->status }}</span>@else<span class="badge bg-danger">{{ $sale->status }}</span>@endif</p></div>
                        <div class="col-md-6"><p><strong>Pelanggan:</strong> {{ $sale->customer?->name ?: 'Penjualan Umum' }}</p><p><strong>Dicatat Oleh:</strong> {{ $sale->user?->name ?: 'N/A' }}</p></div>
                        @if($sale->notes)<div class="col-12"><p><strong>Catatan:</strong> {{ $sale->notes }}</p></div>@endif
                    </div>

                    {{-- [MODIFIKASI] Detail Pembayaran --}}
                    <div class="row pt-3 mt-3 border-top">
                        <h5 class="mb-3">Informasi Pembayaran</h5>
                        <div class="col-md-4"><p class="mb-1"><strong>Status Pembayaran:</strong> 
                            @if($sale->payment_status == 'Lunas')
                                <span class="badge bg-primary">Lunas</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Lunas</span>
                            @endif
                        </p></div>
                        <div class="col-md-4"><p class="mb-1"><strong>Metode Pembayaran:</strong> {{ $sale->payment_method }}</p></div>
                        <div class="col-md-4"><p class="mb-1"><strong>Jumlah Dibayar:</strong> Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</p></div>
                        @if($sale->payment_status == 'Belum Lunas')
                            <div class="col-12 mt-2"><p class="mb-1 fw-bold text-danger"><strong>Sisa Tagihan:</strong> Rp {{ number_format($sale->total_amount - $sale->amount_paid, 0, ',', '.') }}</p></div>
                        @endif
                    </div>
                    <hr class="mb-4">

                    <h5 class="mb-3">Rincian Produk yang Dijual</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light"><tr><th scope="col" style="width: 5%;">No.</th><th scope="col">Nama Produk</th><th scope="col" class="text-center">Jumlah</th><th scope="col" class="text-end">Harga Jual Satuan</th><th scope="col" class="text-end">Subtotal</th></tr></thead>
                            <tbody>@foreach ($sale->details as $index => $detail)<tr class="{{ $sale->status != 'Completed' ? 'text-muted' : '' }}"><th scope="row">{{ $index + 1 }}</th><td class="{{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td><td class="text-center {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td><td class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td><td class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td></tr>@endforeach</tbody>
                            <tfoot><tr class="table-dark"><th colspan="4" class="text-end">TOTAL PENJUALAN</th><th class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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