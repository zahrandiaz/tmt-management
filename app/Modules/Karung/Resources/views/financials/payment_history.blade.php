{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Pembayaran
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Riwayat Pembayaran untuk #{{ $type === 'sales' ? $transaction->invoice_number : $transaction->purchase_code }}
                            </h5>
                            @if ($type === 'sales')
                                <a href="{{ route('karung.financials.receivables') }}" class="btn btn-secondary btn-sm">
                            @else
                                <a href="{{ route('karung.financials.payables') }}" class="btn btn-secondary btn-sm">
                            @endif
                                <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                                </a>
                        </div>
                        <div class="card-body">
                            {{-- Ringkasan Transaksi Induk --}}
                            <div class="row mb-4 p-3 bg-light rounded">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>{{ $type === 'sales' ? 'Pelanggan' : 'Supplier' }}:</strong> {{ ($type === 'sales' ? $transaction->customer->name : $transaction->supplier->name) ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Tanggal Transaksi:</strong> {{ $transaction->transaction_date->format('d M Y') }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p class="mb-1"><strong>Total Tagihan:</strong> Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                    <p class="mb-1"><strong>Total Dibayar:</strong> Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</p>
                                    <p class="mb-0 fw-bold {{ $transaction->payment_status === 'Lunas' ? 'text-success' : 'text-danger' }}">
                                        <strong>Sisa Tagihan:</strong> Rp {{ number_format($transaction->total_amount - $transaction->amount_paid, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Tabel Riwayat Pembayaran --}}
                            <h6 class="mb-3">Detail Pembayaran:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tanggal Bayar</th>
                                            <th class="text-end">Jumlah</th>
                                            <th>Metode</th>
                                            <th>Catatan</th>
                                            <th>Dicatat oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($paymentHistories as $history)
                                        <tr>
                                            <td>{{ $history->payment_date->format('d M Y') }}</td>
                                            <td class="text-end">Rp {{ number_format($history->amount, 0, ',', '.') }}</td>
                                            <td><span class="badge {{ $history->payment_method === 'Nota Kredit' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $history->payment_method ?: '-' }}</span></td>
                                            <td>{{ $history->notes ?: '-' }}</td>
                                            <td>{{ $history->user->name ?? 'N/A' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada riwayat pembayaran.</td>
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
    </x-module-layout>
</x-app-layout>