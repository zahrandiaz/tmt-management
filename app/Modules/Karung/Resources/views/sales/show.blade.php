{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Detail Transaksi Penjualan: #{{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Detail Penjualan: #{{ $sale->invoice_number }}</h5>
                            <div class="no-print">
                                <a href="{{ route('karung.sales.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar
                                </a>

                                @can('karung.manage_returns')
                                    @if($sale->status == 'Completed')
                                        <a href="{{ route('karung.sales.returns.create', $sale->id) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-box-arrow-in-left me-1"></i> Buat Retur
                                        </a>
                                    @endif
                                @endcan

                                <a href="{{ route('karung.sales.print.thermal', $sale) }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="bi bi-printer-fill me-1"></i> Cetak Struk (58mm)
                                </a>
                                <a href="{{ route('karung.sales.download.pdf', $sale) }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($sale->status != 'Completed')
                                <div class="alert alert-danger"><strong>Transaksi {{ $sale->status }}!</strong> Transaksi ini telah ditandai sebagai '{{ $sale->status }}' dan tidak lagi dihitung dalam laporan.</div>
                            @endif

                            {{-- Informasi Utama --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>No. Invoice:</strong> {{ $sale->invoice_number }}</p>
                                    <p class="mb-1"><strong>Tanggal Transaksi:</strong> {{ $sale->transaction_date->format('d F Y, H:i') }}</p>
                                    <p class="mb-1"><strong>Status:</strong>
                                        @if($sale->status == 'Completed')
                                            <span class="badge bg-success">{{ $sale->status }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $sale->status }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Pelanggan:</strong> {{ $sale->customer?->name ?: 'Penjualan Umum' }}</p>
                                    <p class="mb-1"><strong>Dicatat Oleh:</strong> {{ $sale->user?->name ?: 'N/A' }}</p>
                                </div>
                                @if($sale->notes)
                                    <div class="col-12 mt-2">
                                        <p class="mb-1"><strong>Catatan:</strong> {{ $sale->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Informasi Pembayaran --}}
                            <div class="row pt-3 mt-3 border-top">
                                <h5 class="mb-3">Informasi Pembayaran</h5>
                                <div class="col-md-4"><p class="mb-1"><strong>Status Pembayaran:</strong>
                                    @if($sale->payment_status == 'Lunas')
                                        <span class="badge bg-primary">Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                                    @endif
                                </p></div>
                                <div class="col-md-4"><p class="mb-1"><strong>Metode Pembayaran:</strong> {{ $sale->payment_method }}</p></div>
                                <div class="col-md-4"><p class="mb-1"><strong>Jumlah Dibayar:</strong> Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</p></div>
                                @if($sale->payment_status == 'Belum Lunas')
                                    <div class="col-12 mt-2"><p class="mb-1 fw-bold text-danger"><strong>Sisa Tagihan:</strong> Rp {{ number_format($sale->total_amount - $sale->amount_paid, 0, ',', '.') }}</p></div>
                                @endif
                            </div>
                            <hr class="mb-4">

                            {{-- Rincian Produk --}}
                            <h5 class="mb-3">Rincian Produk yang Dijual</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 5%;">No.</th>
                                            <th scope="col">Nama Produk</th>
                                            <th scope="col" class="text-center">Jumlah</th>
                                            <th scope="col" class="text-end">Harga Jual Satuan</th>
                                            <th scope="col" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sale->details as $index => $detail)
                                            <tr class="{{ $sale->status != 'Completed' ? 'text-muted' : '' }}">
                                                <th scope="row">{{ $index + 1 }}</th>
                                                <td class="{{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                                <td class="text-center {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td>
                                                <td class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                                <td class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <th colspan="4" class="text-end">TOTAL PENJUALAN</th>
                                            <th class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>