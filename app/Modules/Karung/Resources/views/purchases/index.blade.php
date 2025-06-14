@extends('karung::layouts.karung_app')

@section('title', 'Riwayat Transaksi Pembelian - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Transaksi Pembelian</h5>
                    <div>
                        <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm no-print">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                                <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                            </svg>
                            Kembali
                        </a>
                        <a href="{{ route('karung.purchases.create') }}" class="btn btn-light btn-sm no-print">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                            </svg>
                            Catat Pembelian Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ $status == 'Completed' ? 'active' : '' }}" href="{{ route('karung.purchases.index', ['status' => 'Completed']) }}">Selesai</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status == 'Cancelled' ? 'active' : '' }}" href="{{ route('karung.purchases.index', ['status' => 'Cancelled']) }}">Dibatalkan</a>
                        </li>
                    </ul>

                    <div class="mb-4">
                        <form action="{{ route('karung.purchases.index') }}" method="GET">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan Kode Pembelian, No. Referensi, atau Nama Supplier..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Kode Pembelian</th>
                                    <th scope="col">No. Referensi</th>
                                    <th scope="col">Supplier</th>
                                    <th scope="col">Produk Dibeli</th>
                                    <th scope="col" class="text-end">Total</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" style="width: 15%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchases as $purchase)
                                    <tr class="{{ $purchase->status == 'Cancelled' ? 'table-secondary text-muted' : '' }}">
                                        <td>{{ $purchase->transaction_date->format('d-m-Y H:i') }}</td>
                                        <td><strong>{{ $purchase->purchase_code }}</strong></td>
                                        <td>{{ $purchase->purchase_reference_no ?: '-' }}</td>
                                        <td>{{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</td>
                                        <td>
                                            <span class="{{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">
                                                {{ $purchase->details->pluck('product.name')->implode(', ') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="{{ $purchase->status == 'Cancelled' ? 'text-decoration-line-through' : '' }}">
                                                Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($purchase->status == 'Cancelled')
                                                <span class="badge bg-danger">{{ $purchase->status }}</span>
                                            @else
                                                <span class="badge bg-success">{{ $purchase->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('karung.purchases.show', $purchase->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                                            </a>

                                            {{-- [BARU] Tombol Edit hanya muncul untuk yang berhak --}}
                                            @can('karung.edit_purchases')
                                                @if($purchase->status == 'Completed')
                                                    <a href="{{ route('karung.purchases.edit', $purchase->id) }}" class="btn btn-warning btn-sm" title="Edit Transaksi">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            @endcan

                                            @if($purchase->status == 'Completed')
                                            @can('karung.cancel_purchases')
                                            <form action="{{ route('karung.purchases.cancel', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Aksi ini tidak dapat diurungkan.');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm" title="Batalkan Transaksi">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>
                                                </button>
                                            </form>
                                            @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data transaksi pembelian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $purchases->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection