{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Stok: {{ $product->name }}
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
                            <h5 class="mb-0">Riwayat Stok: {{ $product->name }}</h5>
                            <a href="{{ route('karung.reports.stock') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Laporan Stok
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
                                        @forelse ($stockHistory as $index => $history)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $history->date->format('d-m-Y H:i') }}</td>
                                                <td>
                                                    @if($history->type == 'Penjualan')
                                                        <span class="badge bg-danger">Penjualan</span>
                                                    @elseif($history->type == 'Pembelian')
                                                        <span class="badge bg-success">Pembelian</span>
                                                    @elseif($history->type == 'Retur Penjualan')
                                                        <span class="badge bg-info text-dark">Retur Penjualan</span>
                                                    @elseif($history->type == 'Retur Pembelian')
                                                        <span class="badge bg-warning text-dark">Retur Pembelian</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $history->type }}</span>
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
    </x-module-layout>
</x-app-layout>