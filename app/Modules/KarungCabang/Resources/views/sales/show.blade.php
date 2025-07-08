{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Detail Transaksi Penjualan: #{{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karungcabang::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Detail Penjualan: #{{ $sale->invoice_number }}</h5>
                            <div class="no-print">
                                <a href="{{ route('karungcabang.sales.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar
                                </a>

                                @can('karung.manage_returns')
                                    @if($sale->status == 'Completed')
                                        <a href="{{ route('karungcabang.sales.returns.create', $sale->id) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-box-arrow-in-left me-1"></i> Buat Retur
                                        </a>
                                    @endif
                                @endcan

                                <a href="{{ route('karungcabang.sales.print.thermal', $sale) }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="bi bi-printer-fill me-1"></i> Cetak Struk (58mm)
                                </a>
                                <a href="{{ route('karungcabang.sales.download.pdf', $sale) }}" class="btn btn-danger btn-sm">
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
                            <hr>

                            {{-- [MODIFIKASI TOTAL v1.32.0] Blok Rincian Finansial --}}
                            <div class="row mt-4">
                                <div class="col-lg-7">
                                    <h5 class="mb-3">Rincian Produk yang Dijual</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%;">No.</th>
                                                    <th>Nama Produk</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th class="text-end">Harga Jual</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $originalSubtotal = 0;
                                                @endphp
                                                @foreach ($sale->details as $index => $detail)
                                                    @php
                                                        $originalSubtotal += $detail->sub_total;
                                                    @endphp
                                                    <tr class="{{ $sale->status != 'Completed' ? 'text-muted' : '' }}">
                                                        <th>{{ $index + 1 }}</th>
                                                        <td class="{{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                                        <td class="text-center {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td>
                                                        <td class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                                        <td class="text-end {{ $sale->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <h5 class="mb-3">Ringkasan Pembayaran</h5>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td>Subtotal Produk</td>
                                                        <td class="text-end fw-bold">Rp {{ number_format($originalSubtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                    
                                                    @if($sale->returns->isNotEmpty())
                                                        @php
                                                            $totalReturns = $sale->returns->sum('total_amount');
                                                        @endphp
                                                        <tr class="text-danger">
                                                            <td>
                                                                Pengurangan dari Retur
                                                                @foreach($sale->returns as $return)
                                                                    <br><small class="ms-2"><a href="{{ route('karungcabang.returns.sales.show', $return->id) }}" target="_blank" class="text-reset">- {{ $return->return_code }}</a></small>
                                                                @endforeach
                                                            </td>
                                                            <td class="text-end fw-bold">(- Rp {{ number_format($totalReturns, 0, ',', '.') }})</td>
                                                        </tr>
                                                    @endif

                                                    <tr class="border-top">
                                                        <td class="fw-bold">TOTAL TAGIHAN AKHIR</td>
                                                        <td class="text-end fw-bolder fs-5">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Jumlah Dibayar</td>
                                                        <td class="text-end">Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr class="border-top {{ ($sale->total_amount - $sale->amount_paid) > 0 ? 'table-warning' : 'table-success' }}">
                                                        <td class="fw-bold">
                                                            @if( ($sale->amount_paid - $sale->total_amount) > 0 )
                                                                KELEBIHAN BAYAR
                                                            @else
                                                                SISA TAGIHAN
                                                            @endif
                                                        </td>
                                                        <td class="text-end fw-bolder fs-5">
                                                            Rp {{ number_format(abs($sale->total_amount - $sale->amount_paid), 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Status Pembayaran</td>
                                                        <td class="text-end">
                                                            @if($sale->payment_status == 'Lunas')
                                                                <span class="badge bg-primary fs-6">Lunas</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark fs-6">Belum Lunas</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>