@extends('karung::layouts.karung_app')

@section('title', 'Dashboard Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Modul Karung</h1>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            @can('karung.create_sales')
                <a href="{{ route('karung.sales.create') }}" class="btn btn-lg btn-success shadow-sm me-2 mb-2">
                    <i class="bi bi-cart-plus-fill me-2"></i>Catat Penjualan
                </a>
            @endcan
            @can('karung.create_purchases')
                <a href="{{ route('karung.purchases.create') }}" class="btn btn-lg btn-primary shadow-sm me-2 mb-2">
                    <i class="bi bi-truck me-2"></i>Catat Pembelian
                </a>
            @endcan
            @can('karung.manage_products')
                 <a href="{{ route('karung.products.create') }}" class="btn btn-lg btn-warning shadow-sm me-2 mb-2">
                    <i class="bi bi-box-seam-fill me-2"></i>Tambah Produk
                </a>
            @endcan
        </div>
    </div>

    @can('karung.view_reports')
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-5 border-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Pendapatan (Hari Ini)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">Rp {{ number_format($kpiCards['todays_revenue'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-cash-coin fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-5 border-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Transaksi (Hari Ini)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $kpiCards['todays_transactions'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-receipt fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-5 border-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Produk Terjual (Hari Ini)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $kpiCards['todays_products_sold'] ?? 0 }} Unit</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-box-seam fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @if(isset($criticalStockProducts) && $criticalStockProducts->isNotEmpty())
    <div class="alert alert-warning border-0 border-start border-5 border-warning shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="fs-3 me-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <h5 class="alert-heading fw-bold">Peringatan Stok Kritis!</h5>
                <p class="mb-1">Produk berikut memiliki stok di bawah atau sama dengan level minimum. Segera lakukan pemesanan ulang.</p>
                <ul class="mb-0 small ps-3">
                    @foreach ($criticalStockProducts as $product)
                        <li><strong>{{ $product->name }}</strong> - Stok saat ini: {{ $product->stock }} (Minimum: {{ $product->min_stock_level }})</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white"><h6 class="m-0 fw-bold"><i class="bi bi-graph-up me-2"></i>Grafik Penjualan 7 Hari Terakhir</h6></div>
                <div class="card-body">
                    <div style="height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        @can('karung.view_reports')
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white"><h6 class="m-0 fw-bold">5 Produk Terlaris (30 Hari)</h6></div>
                <div class="card-body">
                    @forelse($bestsellingProducts as $item)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span>{{ $loop->iteration }}. {{ $item->product->name ?? 'Produk Dihapus' }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $item->total_sold }} unit</span>
                        </div>
                    @empty
                        <p class="text-center text-muted my-4">Belum ada data penjualan.</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white"><h6 class="m-0 fw-bold">Aktivitas Terbaru</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse($latestActivities as $activity)
                        <li class="list-group-item small">
                            {{ $activity->description }} oleh <strong>{{ $activity->causer->name ?? 'Sistem' }}</strong>.
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $activity->created_at->diffForHumans() }}</div>
                        </li>
                    @empty
                         <li class="list-group-item text-center text-muted">Tidak ada aktivitas terbaru.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection

@push('footer-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($salesChartLabels),
                datasets: [{
                    label: 'Total Penjualan',
                    data: @json($salesChartData),
                    fill: true,
                    borderColor: 'rgb(78, 115, 223)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
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