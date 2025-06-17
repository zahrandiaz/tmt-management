@extends('karung::layouts.karung_app')

@section('title', 'Riwayat Transaksi Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Transaksi Penjualan</h5>
                    <div>
                        <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/></svg>
                            Kembali
                        </a>
                        <a href="{{ route('karung.sales.create') }}" class="btn btn-light btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
                            Catat Penjualan Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('karung::components.flash-message')

                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ $status == 'Completed' ? 'active' : '' }}" href="{{ route('karung.sales.index', ['status' => 'Completed']) }}">Selesai</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status == 'Cancelled' ? 'active' : '' }}" href="{{ route('karung.sales.index', ['status' => 'Cancelled']) }}">Dibatalkan</a>
                        </li>
                        @can('karung.delete_sales')
                        <li class="nav-item">
                            <a class="nav-link text-danger {{ $status == 'Deleted' ? 'active' : '' }}" href="{{ route('karung.sales.index', ['status' => 'Deleted']) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill me-1" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                                Dihapus (Sampah)
                            </a>
                        </li>
                        @endcan
                    </ul>
                    
                    <div class="mb-4">
                        <form action="{{ route('karung.sales.index') }}" method="GET">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan No. Invoice atau Nama Pelanggan..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">Cari</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th><th>No. Invoice</th><th>Pelanggan</th><th>Produk Dijual</th>
                                    <th class="text-end">Total</th><th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr class="{{ $sale->status != 'Completed' ? 'table-secondary text-muted' : '' }}">
                                        <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->customer?->name ?: 'Penjualan Umum' }}</td>
                                        <td><span class="{{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $sale->details->pluck('product.name')->implode(', ') }}</span></td>
                                        <td class="text-end"><span class="{{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span></td>
                                        
                                        <td class="text-center">
                                            @if($sale->status == 'Completed')
                                                <span class="badge bg-success">{{ $sale->status }}</span>
                                                @if($sale->payment_status == 'Lunas')
                                                    <span class="badge bg-primary">Lunas</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                                                @endif
                                            @else
                                                <span class="badge bg-danger">{{ $sale->status }}</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($sale->status != 'Deleted')
                                                <a href="{{ route('karung.sales.show', $sale->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg></a>
                                                @if($sale->status == 'Completed')
                                                    {{-- [BARU] Tombol Bayar --}}
                                                    @can('managePayment', $sale)
                                                        @if($sale->payment_status == 'Belum Lunas')
                                                            <button type="button" class="btn btn-success btn-sm pay-button"
                                                                    data-url="{{ route('karung.sales.update_payment', $sale->id) }}"
                                                                    data-sisa="{{ $sale->total_amount - $sale->amount_paid }}"
                                                                    data-invoice="{{ $sale->invoice_number }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/><path d="M9.438 11.944c.047.596.518 1.06 1.062 1.06h.007c.545 0 1.015-.464 1.062-1.06l.062-1.06h-2.19c.034.326.046.654.046.979 0 .206-.008.411-.022.615m-2.92 1.054h1.372v-1.06h-1.062a1 1 0 0 0-.062-1.06H4.58a.5.5 0 0 0-.496.497v.004c0 .596.464 1.06 1.06 1.06h2.712M1.17 8.077a.5.5 0 0 1 .5-.5h9.66a.5.5 0 0 1 0 1H1.671a.5.5 0 0 1-.5-.5m.5 2.408a.5.5 0 0 1 .5-.5h7.66a.5.5 0 0 1 0 1H2.171a.5.5 0 0 1-.5-.5m.5 2.408a.5.5 0 0 1 .5-.5h4.66a.5.5 0 0 1 0 1H2.671a.5.5 0 0 1-.5-.5z"/></svg>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                    @can('karung.edit_sales')
                                                        <a href="{{ route('karung.sales.edit', $sale->id) }}" class="btn btn-warning btn-sm" title="Edit Transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg></a>
                                                    @endcan
                                                    @can('karung.cancel_sales')
                                                        <form action="{{ route('karung.sales.cancel', $sale->id) }}" method="POST" class="d-inline cancel-form">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Batalkan Transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg></button>
                                                        </form>
                                                    @endcan
                                                    @can('karung.delete_sales')
                                                        <form action="{{ route('karung.sales.destroy', $sale->id) }}" method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-dark btn-sm" title="Hapus Transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.024l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m3.5-.05l-.5 8.5a.5.5 0 1 0 .998.06l.5-8.5a.5.5 0 1 0-.998-.06m3.5.002l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998-.06"/></svg></button>
                                                        </form>
                                                    @endcan
                                                @endif
                                            @else
                                                @can('restore', $sale)
                                                    <form action="{{ route('karung.sales.restore', $sale->id) }}" method="POST" class="d-inline restore-form">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" title="Pulihkan Transaksi">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                                                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                                                                <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted fst-italic">Tidak ada aksi</span>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">Tidak ada data transaksi penjualan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $sales->appends(request()->query())->links() }}
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
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function (event) {
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
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.cancel-form').forEach(form => {
            form.addEventListener('submit', function (event) {
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
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.restore-form').forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Pulihkan Transaksi?',
                    text: "Transaksi akan dikembalikan ke status 'Completed' dan stok akan disesuaikan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Pulihkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // [BARU] Script untuk tombol bayar
        document.querySelectorAll('.pay-button').forEach(button => {
            button.addEventListener('click', function (event) {
                const url = this.dataset.url;
                const sisaTagihan = parseFloat(this.dataset.sisa);
                const invoice = this.dataset.invoice;

                Swal.fire({
                    title: 'Update Pembayaran',
                    html: `
                        <p class="mb-1">Invoice: <strong>#${invoice}</strong></p>
                        <p>Sisa Tagihan: <strong>Rp ${new Intl.NumberFormat('id-ID').format(sisaTagihan)}</strong></p>
                        <input type="number" id="new_payment_amount" class="swal2-input" placeholder="Masukkan jumlah pembayaran" required min="0" max="${sisaTagihan}">
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Pembayaran',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const newPaymentAmount = Swal.getPopup().querySelector('#new_payment_amount').value;
                        if (!newPaymentAmount || newPaymentAmount <= 0) {
                            Swal.showValidationMessage(`Jumlah pembayaran tidak valid`);
                        } else if (parseFloat(newPaymentAmount) > sisaTagihan) {
                             Swal.showValidationMessage(`Pembayaran tidak boleh melebihi sisa tagihan`);
                        }
                        return newPaymentAmount;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;
                        
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        const amountInput = document.createElement('input');
                        amountInput.type = 'hidden';
                        amountInput.name = 'new_payment_amount';
                        amountInput.value = result.value;
                        form.appendChild(amountInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush