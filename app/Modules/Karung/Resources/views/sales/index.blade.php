@extends('layouts.tmt_app') {{-- Menggunakan layout utama TMT --}}

@section('title', 'Riwayat Transaksi Penjualan - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Transaksi Penjualan</h5>
                    <a href="{{ route('karung.sales.create') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                        </svg>
                        Catat Penjualan Baru
                    </a>
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" style="width: 5%;">No.</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">No. Invoice</th>
                                    <th scope="col">Pelanggan</th>
                                    <th scope="col" class="text-end">Total Penjualan</th>
                                    <th scope="col">Dicatat Oleh</th>
                                    <th scope="col" style="width: 15%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $index => $sale)
                                    <tr>
                                        <th scope="row">{{ $sales->firstItem() + $index }}</th>
                                        <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->customer?->name ?: 'Penjualan Umum' }}</td>
                                        <td class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        <td>{{ $sale->user?->name ?: 'N/A' }}</td>
                                        <td class="text-center">
                                            {{-- Tombol Detail/Show - Rute akan kita buat nanti --}}
                                            <a href="{{ route('karung.sales.show', $sale->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                                </svg>
                                            </a>
                                            {{-- Untuk transaksi Penjualan, fitur Edit & Hapus akan kita tunda sesuai kesepakatan --}}
                                            {{-- <a href="{{ route('karung.sales.edit', $sale->id) }}" class="btn btn-warning btn-sm" title="Edit">...</a> --}}
                                            {{-- <form action="{{ route('karung.sales.destroy', $sale->id) }}" method="POST" class="d-inline" ...>...</form> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data transaksi penjualan.</td> {{-- Sesuaikan colspan --}}
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection