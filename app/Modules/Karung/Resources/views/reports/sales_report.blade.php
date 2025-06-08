@extends('layouts.tmt_app')

@section('title', 'Laporan Penjualan - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Laporan Penjualan</h5>
                </div>
                <div class="card-body">
                    {{-- Form Filter Tanggal --}}
                    <form action="{{ route('karung.reports.sales') }}" method="GET" class="mb-4">
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
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h6 class="card-title">Total Omzet Penjualan</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
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
                                    <th scope="col">No. Invoice</th>
                                    <th scope="col">Pelanggan</th>
                                    <th scope="col" class="text-end">Total Penjualan</th>
                                    <th scope="col">Dicatat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->customer?->name ?: 'Penjualan Umum' }}</td>
                                        <td class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        <td>{{ $sale->user?->name ?: 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data transaksi penjualan pada rentang tanggal yang dipilih.</td>
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