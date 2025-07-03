{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Transaksi Penjualan
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Riwayat Transaksi Penjualan</h5>
                            <div>
                                <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                                </a>
                                @can('karung.create_sales')
                                <a href="{{ route('karung.sales.create') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-plus-circle-fill"></i> Catat Penjualan Baru
                                </a>
                                @endcan
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
                                        <i class="bi bi-trash-fill me-1"></i> Dihapus (Sampah)
                                    </a>
                                </li>
                                @endcan
                            </ul>

                            <div class="mb-4">
                                <form action="{{ route('karung.sales.index') }}" method="GET">
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan No. Invoice atau Nama Pelanggan..." value="{{ request('search') }}">
                                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>No. Invoice</th>
                                            <th>Pelanggan</th>
                                            <th>Produk Dijual</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-center">Status</th>
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
                                                        <a href="{{ route('karung.sales.show', $sale->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail"><i class="bi bi-eye-fill"></i></a>
                                                        @if($sale->status == 'Completed')
                                                            @can('managePayment', $sale)
                                                                @if($sale->payment_status == 'Belum Lunas')
                                                                    <button type="button" class="btn btn-success btn-sm pay-button"
                                                                            data-url="{{ route('karung.sales.update_payment', $sale->id) }}"
                                                                            data-sisa="{{ $sale->total_amount - $sale->amount_paid }}"
                                                                            data-invoice="{{ $sale->invoice_number }}" title="Catat Pembayaran">
                                                                        <i class="bi bi-cash-coin"></i>
                                                                    </button>
                                                                @endif
                                                            @endcan
                                                            @can('karung.edit_sales')
                                                                <a href="{{ route('karung.sales.edit', $sale->id) }}" class="btn btn-warning btn-sm" title="Edit Transaksi"><i class="bi bi-pencil-square"></i></a>
                                                            @endcan
                                                            @can('karung.cancel_sales')
                                                                <form action="{{ route('karung.sales.cancel', $sale->id) }}" method="POST" class="d-inline cancel-form">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-sm" title="Batalkan Transaksi"><i class="bi bi-x-circle"></i></button>
                                                                </form>
                                                            @endcan
                                                            @can('karung.delete_sales')
                                                                <form action="{{ route('karung.sales.destroy', $sale->id) }}" method="POST" class="d-inline delete-form">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-dark btn-sm" title="Hapus Transaksi"><i class="bi bi-trash3-fill"></i></button>
                                                                </form>
                                                            @endcan
                                                        @endif
                                                    @else
                                                        @can('restore', $sale)
                                                            <form action="{{ route('karung.sales.restore', $sale->id) }}" method="POST" class="d-inline restore-form">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm" title="Pulihkan Transaksi"><i class="bi bi-arrow-counterclockwise"></i></button>
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
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Fungsi konfirmasi untuk form dengan class .delete-form
                document.querySelectorAll('.delete-form').forEach(form => {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'PERINGATAN!',
                            text: "Menghapus transaksi akan menyembunyikannya dari daftar. Anda yakin?",
                            icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
                        }).then((result) => { if (result.isConfirmed) { form.submit(); } });
                    });
                });

                // Fungsi konfirmasi untuk form dengan class .cancel-form
                document.querySelectorAll('.cancel-form').forEach(form => {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Anda yakin?',
                            text: "Transaksi ini akan dibatalkan. Aksi ini tidak dapat diurungkan.",
                            icon: 'question', showCancelButton: true, confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Batalkan!', cancelButtonText: 'Tidak'
                        }).then((result) => { if (result.isConfirmed) { form.submit(); } });
                    });
                });

                // Fungsi konfirmasi untuk form dengan class .restore-form
                document.querySelectorAll('.restore-form').forEach(form => {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Pulihkan Transaksi?',
                            text: "Transaksi akan dikembalikan ke status 'Completed' dan stok akan disesuaikan.",
                            icon: 'question', showCancelButton: true, confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Pulihkan!', cancelButtonText: 'Batal'
                        }).then((result) => { if (result.isConfirmed) { form.submit(); } });
                    });
                });

                // Script untuk tombol bayar
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
                            icon: 'info', showCancelButton: true, confirmButtonText: 'Simpan Pembayaran',
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
    </x-slot>
</x-app-layout>