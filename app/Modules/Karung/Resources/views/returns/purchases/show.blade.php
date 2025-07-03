{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Detail Retur Pembelian: #{{ $purchaseReturn->return_code }}
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
                            <h5 class="mb-0">Detail Retur: #{{ $purchaseReturn->return_code }}</h5>
                            <div>
                                <a href="{{ route('karung.returns.purchases.debit_note.pdf', $purchaseReturn) }}" class="btn btn-danger btn-sm" target="_blank">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Download Nota Debit
                                </a>
                                <a href="{{ route('karung.returns.purchases.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left-circle"></i> Kembali ke Riwayat Retur</a>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Informasi Utama --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Kode Retur:</strong> {{ $purchaseReturn->return_code }}</p>
                                    <p class="mb-1"><strong>Tanggal Retur:</strong> {{ $purchaseReturn->return_date->format('d F Y') }}</p>
                                    <p class="mb-1"><strong>Pembelian Asli:</strong> <a href="{{ route('karung.purchases.show', $purchaseReturn->originalTransaction->id) }}">{{ $purchaseReturn->originalTransaction->purchase_code }}</a></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Supplier:</strong> {{ $purchaseReturn->supplier->name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Dicatat Oleh:</strong> {{ $purchaseReturn->user->name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Alasan:</strong> {{ $purchaseReturn->reason ?: '-' }}</p>
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
                                        @foreach($purchaseReturn->details as $index => $detail)
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
                                            <th class="text-end">Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}</th>
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