{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Detail Transaksi: #{{ $purchase->purchase_code }}
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
                            <h5 class="mb-0">Detail Pembelian: #{{ $purchase->purchase_code }}</h5>
                            <div class="no-print">
                                <a href="{{ route('karung.purchases.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar
                                </a>
                                @can('karung.manage_returns')
                                    @if($purchase->status == 'Completed')
                                        <a href="{{ route('karung.purchases.returns.create', $purchase->id) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-box-arrow-in-left me-1"></i> Buat Retur
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            @if($purchase->status != 'Completed')
                                <div class="alert alert-danger"><strong>Transaksi {{ $purchase->status }}!</strong> Transaksi ini telah ditandai sebagai '{{ $purchase->status }}' dan tidak lagi dihitung dalam laporan.</div>
                            @endif

                            {{-- Informasi Utama --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Kode Pembelian:</strong> <span class="badge bg-dark fs-6">{{ $purchase->purchase_code }}</span></p>
                                    <p class="mb-1"><strong>Tanggal Transaksi:</strong> {{ $purchase->transaction_date->format('d F Y, H:i') }}</p>
                                    <p class="mb-1"><strong>Supplier:</strong> {{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>No. Referensi:</strong> {{ $purchase->purchase_reference_no ?: '-' }}</p>
                                    <p class="mb-1"><strong>Dicatat Oleh:</strong> {{ $purchase->user?->name ?: 'N/A' }}</p>
                                    <p class="mb-1"><strong>Status:</strong>
                                        @if($purchase->status == 'Completed')
                                            <span class="badge bg-success">{{ $purchase->status }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $purchase->status }}</span>
                                        @endif
                                    </p>
                                </div>
                                @if($purchase->notes)
                                    <div class="col-12 mt-2">
                                        <p class="mb-1"><strong>Catatan:</strong> {{ $purchase->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Informasi Pembayaran --}}
                            <div class="row pt-3 mt-3 border-top">
                                <h5 class="mb-3">Informasi Pembayaran</h5>
                                <div class="col-md-4"><p class="mb-1"><strong>Status Pembayaran:</strong>
                                    @if($purchase->payment_status == 'Lunas')
                                        <span class="badge bg-primary">Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                                    @endif
                                </p></div>
                                <div class="col-md-4"><p class="mb-1"><strong>Metode Pembayaran:</strong> {{ $purchase->payment_method }}</p></div>
                                <div class="col-md-4"><p class="mb-1"><strong>Jumlah Dibayar:</strong> Rp {{ number_format($purchase->amount_paid, 0, ',', '.') }}</p></div>
                                @if($purchase->payment_status == 'Belum Lunas')
                                    <div class="col-12 mt-2"><p class="mb-1 fw-bold text-danger"><strong>Sisa Tagihan:</strong> Rp {{ number_format($purchase->total_amount - $purchase->amount_paid, 0, ',', '.') }}</p></div>
                                @endif
                            </div>
                            <hr class="mb-4">

                            {{-- Rincian Produk --}}
                            <h5 class="mb-3">Rincian Produk yang Dibeli</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 5%;">No.</th>
                                            <th scope="col">Nama Produk</th>
                                            <th scope="col" class="text-center">Jumlah</th>
                                            <th scope="col" class="text-end">Harga Beli Satuan</th>
                                            <th scope="col" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchase->details as $index => $detail)
                                            <tr class="{{ $purchase->status != 'Completed' ? 'text-muted' : '' }}">
                                                <th scope="row">{{ $index + 1 }}</th>
                                                <td class="{{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                                <td class="text-center {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">{{ $detail->quantity }}</td>
                                                <td class="text-end {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->purchase_price_at_transaction, 0, ',', '.') }}</td>
                                                <td class="text-end {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <th colspan="4" class="text-end">TOTAL PEMBELIAN</th>
                                            <th class="text-end {{ $purchase->status != 'Completed' ? 'text-decoration-line-through' : '' }}">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if($purchase->attachment_path)
                                <div class="mt-4">
                                    <h5>Lampiran Struk/Nota:</h5>
                                    <a href="{{ asset('storage/' . $purchase->attachment_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $purchase->attachment_path) }}" alt="Lampiran Pembelian" class="img-thumbnail" style="max-width: 300px;">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            // Tidak ada JavaScript khusus untuk halaman ini
        </script>
    </x-slot>
</x-app-layout>