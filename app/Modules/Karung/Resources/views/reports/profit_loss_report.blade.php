@extends('layouts.tmt_app')

@section('title', 'Laporan Laba Rugi Sederhana - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Laporan Laba Rugi Sederhana</h5>
                </div>
                <div class="card-body">
                    {{-- Form Filter Tanggal --}}
                    <form action="{{ route('karung.reports.profit_and_loss') }}" method="GET" class="mb-4">
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
                            </div>
                        </div>
                    </form>

                    <hr>

                    {{-- Ringkasan Laporan --}}
                    <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</h5>
                    <div class="row mb-4 text-center g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h6 class="card-title">(A) Total Pendapatan</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-white bg-secondary">
                                <div class="card-body">
                                    <h6 class="card-title">(B) Total Modal Terjual (HPP)</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-white bg-danger">
                                <div class="card-body">
                                    <h6 class="card-title">(C) Total Pembelian Baru</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-3 col-md-6">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h6 class="card-title">LABA BERSIH (A - B - C)</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Info Kalkulasi Laba Kotor --}}
                    <div class="alert alert-light">
                        <strong>Laba Kotor (A - B):</strong> Rp {{ number_format($grossProfit, 0, ',', '.') }}
                    </div>

                    {{-- Tabel Detail Transaksi --}}
                    <h5 class="mb-3">Rincian Laba per Item Terjual</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Invoice</th>
                                    <th>Produk</th>
                                    <th class="text-center">Jml</th>
                                    <th class="text-end">Harga Jual</th>
                                    <th class="text-end">Harga Beli (Ref.)</th>
                                    <th class="text-end">Laba per Item</th>
                                    <th class="text-end">Subtotal Laba</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesDetails as $detail)
                                @php
                                    // Kalkulasi untuk setiap baris
                                    $purchasePrice = $detail->product?->purchase_price ?? 0;
                                    $profitPerItem = $detail->selling_price_at_transaction - $purchasePrice;
                                    $subTotalProfit = $detail->quantity * $profitPerItem;
                                @endphp
                                    <tr>
                                        <td>{{ $detail->transaction->transaction_date->format('d-m-Y') }}</td>
                                        <td>{{ $detail->transaction->invoice_number }}</td>
                                        <td>{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($purchasePrice, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($profitPerItem, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold {{ $subTotalProfit < 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($subTotalProfit, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data penjualan pada rentang tanggal yang dipilih.</td>
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