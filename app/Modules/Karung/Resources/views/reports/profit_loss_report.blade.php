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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">...</svg>
                        Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
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

                    <div class="mb-4">
                        <strong>Export Laporan:</strong>
                        <a href="{{ route('karung.reports.profit_loss.export', request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
                        <a href="{{ route('karung.reports.profit_loss.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm">PDF</a>
                    </div>
                    <hr>

                    <h5 class="mb-3">Ringkasan Laporan</h5>
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-md-4">
                            <div style="height: 250px;">
                                <canvas id="profitChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card text-white bg-success mb-2">
                                <div class="card-body p-3"><h6 class="card-title mb-0">Total Pendapatan (Omzet)</h6><p class="card-text fs-5 fw-bold mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p></div>
                            </div>
                            {{-- [PERBAIKAN] Mengubah warna kartu modal menjadi merah --}}
                             <div class="card text-white bg-danger mb-2">
                                <div class="card-body p-3"><h6 class="card-title mb-0">Total Modal Terjual (HPP)</h6><p class="card-text fs-5 fw-bold mb-0">Rp {{ number_format($totalCost, 0, ',', '.') }}</p></div>
                            </div>
                             <div class="card text-white bg-primary">
                                <div class="card-body p-3"><h6 class="card-title mb-0">LABA KOTOR</h6><p class="card-text fs-5 fw-bold mb-0">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p></div>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <h5 class="mb-3">Ringkasan Laba per Kategori Produk</h5>
                    <div class="table-responsive mb-5">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Kategori Produk</th>
                                    <th class="text-end">Total Laba Kotor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($profitByCategory as $item)
                                    <tr>
                                        <td>{{ $item['category_name'] }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($item['total_profit'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">Tidak ada data laba per kategori untuk periode ini.</td></tr>
                                @endforelse
                                <tr class="table-dark">
                                    <td class="fw-bold">TOTAL LABA KOTOR</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mb-3">Rincian Laba per Item Terjual</h5>
                    <div class="table-responsive">
                        {{-- Tabel rincian per item tidak saya ubah, hanya memastikan datanya masih benar --}}
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th><th>Invoice</th><th>Produk</th><th class="text-center">Jml</th>
                                    <th class="text-end">Harga Jual</th><th class="text-end">Harga Beli (Ref.)</th>
                                    <th class="text-end">Laba per Item</th><th class="text-end">Subtotal Laba</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesDetails as $detail)
                                @php
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
                                    <tr><td colspan="8" class="text-center">Tidak ada data penjualan pada rentang tanggal yang dipilih.</td></tr>
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

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('profitChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Laba Kotor', 'Total Modal (HPP)'],
                    datasets: [{
                        data: [{{ $grossProfit > 0 ? $grossProfit : 0 }}, {{ $totalCost > 0 ? $totalCost : 0 }}],
                        // [PERBAIKAN] Mengubah warna agar sesuai kartu: Laba (Biru), Modal (Merah)
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.8)', // Primary color for profit
                            'rgba(220, 53, 69, 0.8)',  // Danger color for cost
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(220, 53, 69, 1)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                             callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) { label += ': '; }
                                    if (context.raw !== null) {
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush