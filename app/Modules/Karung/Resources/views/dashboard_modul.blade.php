@extends('karung::layouts.karung_app')

@section('title', 'Dashboard Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            {{-- Blok Notifikasi Stok Kritis (Sudah Ada) --}}
            @if(isset($criticalStockProducts) && $criticalStockProducts->isNotEmpty())
            <div class="alert alert-warning border-0 border-start border-5 border-warning shadow-sm mb-4" role="alert">
                {{-- ... Konten Notifikasi ... --}}
                <div class="d-flex align-items-center">
                    <div class="fs-3 me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="alert-heading fw-bold">Peringatan Stok Kritis!</h5>
                        <p class="mb-1">Produk berikut memiliki stok di bawah atau sama dengan level minimum. Segera lakukan pemesanan ulang.</p>
                        <ul class="mb-0 small">
                            @foreach ($criticalStockProducts as $product)
                                <li>
                                    <strong>{{ $product->name }}</strong> - Stok saat ini: {{ $product->stock }} (Minimum: {{ $product->min_stock_level }})
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up me-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                        Grafik Penjualan 7 Hari Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">{{ __('Dashboard Modul Toko Karung') }}</div>
                <div class="card-body">
                    <h1>🎉 Selamat Datang di Dashboard Modul Toko Karung! 🎉</h1>
                    <p class="lead">Ini adalah pusat kendali untuk semua fitur manajemen toko karung Anda.</p>
                    <hr>
                    {{-- ... (Sisa isi card menu tidak berubah) ... --}}
                    @canany(['karung.create_purchases', 'karung.view_purchases', 'karung.create_sales', 'karung.view_sales'])
                    <h5 class="mt-4">Menu Transaksi</h5>
                    <div class="list-group">
                        @canany(['karung.create_purchases', 'karung.view_purchases'])
                        <a href="{{ route('karung.purchases.index') }}" class="list-group-item list-group-item-action fw-bold">Manajemen Pembelian</a>
                        @endcanany
                        @canany(['karung.create_sales', 'karung.view_sales'])
                        <a href="{{ route('karung.sales.index') }}" class="list-group-item list-group-item-action fw-bold">Manajemen Penjualan</a>
                        @endcanany
                    </div>
                    @endcanany
                    @can('karung.view_reports')
                    <h5 class="mt-4">Menu Laporan</h5>
                    <div class="list-group">
                        <a href="{{ route('karung.reports.sales') }}" class="list-group-item list-group-item-action">Laporan Penjualan</a>
                        <a href="{{ route('karung.reports.purchases') }}" class="list-group-item list-group-item-action">Laporan Pembelian</a>
                        <a href="{{ route('karung.reports.stock') }}" class="list-group-item list-group-item-action">Laporan Stok</a>
                        <a href="{{ route('karung.reports.profit_and_loss') }}" class="list-group-item list-group-item-action">Laporan Laba Rugi</a>
                    </div>
                    @endcan
                    @canany(['karung.manage_products', 'karung.manage_categories', 'karung.manage_types', 'karung.manage_suppliers', 'karung.manage_customers'])
                    <h5 class="mt-4">Menu Master Data</h5>
                    <div class="list-group">
                        @can('karung.manage_products')
                        <a href="{{ route('karung.products.index') }}" class="list-group-item list-group-item-action">⭐ Manajemen Produk Utama</a>
                        @endcan
                        @can('karung.manage_categories')
                        <a href="{{ route('karung.product-categories.index') }}" class="list-group-item list-group-item-action">Manajemen Kategori Produk</a>
                        @endcan
                        @can('karung.manage_types')
                        <a href="{{ route('karung.product-types.index') }}" class="list-group-item list-group-item-action">Manajemen Jenis Produk</a>
                        @endcan
                        @can('karung.manage_suppliers')
                        <a href="{{ route('karung.suppliers.index') }}" class="list-group-item list-group-item-action">Manajemen Supplier</a>
                        @endcan
                        @can('karung.manage_customers')
                        <a href="{{ route('karung.customers.index') }}" class="list-group-item list-group-item-action">Manajemen Pelanggan</a>
                        @endcan
                    </div>
                    @endcanany
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const salesLabels = @json($salesChartLabels);
        const salesData = @json($salesChartData);

        const ctx = document.getElementById('salesChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Total Penjualan',
                    data: salesData,
                    fill: true,
                    borderColor: 'rgb(23, 162, 184)',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- INI PERBAIKANNYA
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                         callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
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
    });
</script>
@endpush