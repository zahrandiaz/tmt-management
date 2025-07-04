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
                                                            @if($sale->payment_status == 'Belum Lunas')
                                                                @can('managePayment', $sale)
                                                                    <button type="button" class="btn btn-success btn-sm" title="Catat Pembayaran"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#paymentModal"
                                                                            data-transaction-id="{{ $sale->id }}"
                                                                            data-transaction-type="sales"
                                                                            data-max-amount="{{ $sale->total_amount - $sale->amount_paid }}"
                                                                            data-invoice-number="{{ $sale->invoice_number }}">
                                                                        <i class="bi bi-cash-coin"></i>
                                                                    </button>
                                                                @endcan
                                                            @endif
                                                            @can('karung.edit_sales')
                                                                <a href="{{ route('karung.sales.edit', $sale->id) }}" class="btn btn-warning btn-sm" title="Edit Transaksi"><i class="bi bi-pencil-square"></i></a>
                                                            @endcan
                                                            @can('karung.cancel_sales')
                                                                <form action="{{ route('karung.sales.cancel', $sale->id) }}" method="POST" class="d-inline needs-confirmation"
                                                                      data-confirm-title="Anda yakin?"
                                                                      data-confirm-text="Transaksi ini akan dibatalkan. Aksi ini tidak dapat diurungkan."
                                                                      data-confirm-button-text="Ya, Batalkan!">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-sm" title="Batalkan Transaksi"><i class="bi bi-x-circle"></i></button>
                                                                </form>
                                                            @endcan
                                                            @can('karung.delete_sales')
                                                                <form action="{{ route('karung.sales.destroy', $sale->id) }}" method="POST" class="d-inline needs-confirmation"
                                                                      data-confirm-title="PERINGATAN!"
                                                                      data-confirm-text="Menghapus transaksi akan menyembunyikannya dari daftar. Anda yakin?"
                                                                      data-confirm-button-text="Ya, Hapus!">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-dark btn-sm" title="Hapus Transaksi"><i class="bi bi-trash3-fill"></i></button>
                                                                </form>
                                                            @endcan
                                                        @endif
                                                    @else
                                                        @can('restore', $sale)
                                                            <form action="{{ route('karung.sales.restore', $sale->id) }}" method="POST" class="d-inline needs-confirmation"
                                                                  data-confirm-icon="question"
                                                                  data-confirm-title="Pulihkan Transaksi?"
                                                                  data-confirm-text="Transaksi akan dikembalikan ke status 'Completed' dan stok akan disesuaikan."
                                                                  data-confirm-button-text="Ya, Pulihkan!"
                                                                  data-confirm-button-color="#198754">
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

        @include('karung::components.payment-modal')
    </x-module-layout>

    <x-slot name="scripts">
        @stack('scripts')
    </x-slot>
</x-app-layout>