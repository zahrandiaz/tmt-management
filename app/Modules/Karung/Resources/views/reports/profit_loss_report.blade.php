@extends('karung::layouts.karung_app')

@section('title', 'Laporan Laba Rugi - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Laba Rugi</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">Kembali ke Dashboard</a>
                </div>
                <div class="card-body">
                    {{-- Bagian Filter Tanggal (Tidak Berubah) --}}
                    <div class="mb-3">
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'today']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_week']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_month']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_year']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                    </div>

                    <form method="GET" action="{{ route('karung.reports.profit_and_loss') }}" class="mb-4 p-3 border rounded">
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
                                <button type="submit" class="btn btn-primary w-100">Filter Manual</button>
                            </div>
                        </div>
                    </form>

                    {{-- Tombol Export (Tidak Berubah) --}}
                    <div class="mb-4">
                        <strong>Export Laporan:</strong>
                        @php
                            $exportParams = array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate]);
                        @endphp
                        <a href="{{ route('karung.reports.profit_loss.export', $exportParams) }}" class="btn btn-success btn-sm"><svg...></svg> Excel</a>
                        <a href="{{ route('karung.reports.profit_loss.export.pdf', $exportParams) }}" class="btn btn-danger btn-sm"><svg...></svg> PDF</a>
                    </div>
                    <hr>

                    {{-- [MODIFIKASI v1.30] Bagian Ringkasan Laporan dengan Data Retur --}}
                    <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}</h5>
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-md-4">
                            <div style="height: 250px;"><canvas id="profitChart"></canvas></div>
                        </div>
                        <div class="col-md-8">
                            <div class="card border-success border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>Pendapatan Kotor (Omzet)</span> <span class="fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card border-warning border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(-) Total Retur Penjualan</span> <span class="fw-bold">Rp {{ number_format($totalReturns, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card bg-light border-dark border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(=) Pendapatan Bersih</span> <span class="fw-bold fs-5">Rp {{ number_format($netRevenue, 0, ',', '.') }}</span></div>
                            </div></div>
                             <div class="card border-secondary border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(-) HPP Bersih</span> <span class="fw-bold">Rp {{ number_format($netCostOfGoodsSold, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card bg-light border-info border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(=) Laba Kotor</span> <span class="fw-bold fs-5 text-info">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span></div>
                            </div></div>
                             <div class="card border-danger border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(-) Biaya Operasional</span> <span class="fw-bold">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card text-white {{ $netProfit >= 0 ? 'bg-primary' : 'bg-danger' }}"><div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center"><h6 class="card-title mb-0">(=) LABA BERSIH</h6><p class="card-text fs-4 fw-bold mb-0">Rp {{ number_format($netProfit, 0, ',', '.') }}</p></div>
                            </div></div>
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
                                    $purchasePrice = $detail->purchase_price_at_sale; 
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
                    // [MODIFIKASI v1.30] Update data chart agar lebih relevan
                    labels: ['Laba Kotor', 'HPP Bersih', 'Biaya Operasional'],
                    datasets: [{
                        data: [
                            {{ $grossProfit > 0 ? $grossProfit : 0 }}, 
                            {{ $netCostOfGoodsSold > 0 ? $netCostOfGoodsSold : 0 }},
                            {{ $totalExpenses > 0 ? $totalExpenses : 0 }}
                        ],
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.8)', // Biru untuk Laba
                            'rgba(220, 53, 69, 0.8)',   // Merah untuk HPP
                            'rgba(255, 193, 7, 0.8)',   // Kuning untuk Biaya
                        ],
                        borderColor: ['#FFFFFF'],
                        borderWidth: 2
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