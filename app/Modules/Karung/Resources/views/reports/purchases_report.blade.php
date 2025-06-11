@extends('karung::layouts.karung_app')

@section('title', 'Laporan Pembelian - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Pembelian</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    {{-- Form Filter Tanggal --}}
                    <form action="{{ route('karung.reports.purchases') }}" method="GET" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
                                {{-- Nanti bisa tambah tombol Export PDF/Excel di sini --}}
                            </div>
                        </div>
                    </form>

                    <hr>

                    {{-- Ringkasan Laporan --}}
                    <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card text-white bg-danger"> {{-- Ubah warna untuk pengeluaran --}}
                                <div class="card-body">
                                    <h6 class="card-title">Total Pengeluaran Pembelian</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($totalSpending, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h6 class="card-title">Jumlah Transaksi</h6>
                                    <p class="card-text fs-4 fw-bold">{{ $totalTransactions }} Transaksi</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Detail Transaksi --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">No. Referensi</th>
                                    <th scope="col">Supplier</th>
                                    <th scope="col" class="text-end">Total Pembelian</th>
                                    <th scope="col">Dicatat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->transaction_date->format('d-m-Y') }}</td>
                                        <td>{{ $purchase->purchase_reference_no ?: '-' }}</td>
                                        <td>{{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</td>
                                        <td class="text-end">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                        <td>{{ $purchase->user?->name ?: 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data transaksi pembelian pada rentang tanggal yang dipilih.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection