{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Detail Retur Penjualan: #{{ $salesReturn->return_code }}
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
                            <h5 class="mb-0">Detail Retur: #{{ $salesReturn->return_code }}</h5>
                            <div>
                                <a href="{{ route('karung.returns.sales.credit_note.pdf', $salesReturn->id) }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Nota Kredit
                                </a>
                                <a href="{{ route('karung.returns.sales.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left-circle"></i> Kembali ke Riwayat Retur</a>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Informasi Utama --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Kode Retur:</strong> {{ $salesReturn->return_code }}</p>
                                    <p class="mb-1"><strong>Tanggal Retur:</strong> {{ $salesReturn->return_date->format('d F Y') }}</p>
                                    <p class="mb-1"><strong>Invoice Asli:</strong> <a href="{{ route('karung.sales.show', $salesReturn->originalTransaction->id) }}">{{ $salesReturn->originalTransaction->invoice_number }}</a></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Pelanggan:</strong> {{ $salesReturn->customer->name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Dicatat Oleh:</strong> {{ $salesReturn->user->name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Alasan:</strong> {{ $salesReturn->reason ?: '-' }}</p>
                                </div>
                            </div>
                            <hr class="mb-4">

                            {{-- Rincian Produk --}}
                            <h5 class="mb-3">Rincian Produk yang Diretur</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 5%;">No.</th>
                                            <th scope="col">Nama Produk</th>
                                            <th scope="col" class="text-center">Jumlah</th>
                                            <th scope="col" class="text-end">Harga Satuan</th>
                                            <th scope="col" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salesReturn->details as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $detail->product->name ?? 'Produk Telah Dihapus' }}</td>
                                            <td class="text-center">{{ $detail->quantity }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <th colspan="4" class="text-end">TOTAL NILAI RETUR</th>
                                            <th class="text-end">Rp {{ number_format($salesReturn->total_amount, 0, ',', '.') }}</th>
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