@extends('karung::layouts.karung_app')

@section('title', 'Laporan Laba Rugi - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Laba Rugi</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    {{-- [PERBAIKAN] Form filter dan tombol disatukan --}}
                    <form method="GET" action="{{ route('karung.reports.profit_and_loss') }}" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-5">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    {{-- [PERBAIKAN] Grup tombol export diletakkan di sini agar rapi --}}
                    <div class="mb-4">
                        <strong>Export Laporan:</strong>
                        <a href="{{ route('karung.reports.profit_loss.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16">
                                <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/>
                            </svg>
                            Excel
                        </a>
                        <a href="{{ route('karung.reports.profit_loss.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf-fill" viewBox="0 0 16 16">
                                <path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572a.27.27 0 0 1-.035.012.28.28 0 0 1-.031-.023c-.075-.041-.158-.1-.218-.17a.85.85 0 0 1-.135-.37c-.014-.042-.027-.102-.038-.172a.21.21 0 0 1 .035-.145c.022-.02.05-.038.083-.051a.2.2 0 0 1 .051-.028.2.2 0 0 1 .068.004q.032.007.07.02z"/>
                                <path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM.5 11.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                            </svg>
                            PDF
                        </a>
                    </div>
                    
                    <hr>

                    <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}</h5>
                    <div class="row mb-4 text-center g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h6 class="card-title">(A) Total Pendapatan</h6>
                                    <p class="card-text fs-4 fw-bold">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
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

                    <div class="alert alert-light">
                        <strong>Laba Kotor (A - B):</strong> Rp {{ number_format($grossProfit, 0, ',', '.') }}
                    </div>

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