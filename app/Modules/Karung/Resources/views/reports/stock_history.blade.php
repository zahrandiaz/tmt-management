@extends('karung::layouts.karung_app')

@section('title', 'Riwayat Stok - ' . $product->name)

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Stok: {{ $product->name }}</h5>
                    <a href="{{ route('karung.reports.stock') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">...</svg>
                        Kembali ke Laporan Stok
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4"><strong>SKU:</strong> {{ $product->sku }}</div>
                        <div class="col-md-4"><strong>Stok Saat Ini:</strong> <span class="badge bg-primary fs-6">{{ $product->stock }}</span></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th>Tanggal</th>
                                    <th>Tipe Transaksi</th>
                                    <th>No. Referensi</th>
                                    <th class="text-center">Masuk</th>
                                    <th class="text-center">Keluar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockHistory as $history)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $history->date->format('d-m-Y H:i') }}</td>
                                        <td>
                                            @if($history->type == 'Penjualan')
                                                <span class="badge bg-danger">Penjualan</span>
                                            @else
                                                <span class="badge bg-success">Pembelian</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ $history->url }}" target="_blank">{{ $history->reference }}</a></td>
                                        <td class="text-center text-success fw-bold">{{ $history->quantity_in > 0 ? '+'.$history->quantity_in : '-' }}</td>
                                        <td class="text-center text-danger fw-bold">{{ $history->quantity_out > 0 ? '-'.$history->quantity_out : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center">Tidak ada riwayat pergerakan stok untuk produk ini.</td></tr>
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