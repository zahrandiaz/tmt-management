@extends('layouts.tmt_app')

@section('title', 'Dashboard Modul Toko Karung')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">{{ __('Dashboard Modul Toko Karung') }}</div>

                <div class="card-body">
                    <h1>🎉 Selamat Datang di Dashboard Modul Toko Karung! 🎉</h1>
                    <p class="lead">Ini adalah pusat kendali untuk semua fitur manajemen toko karung Anda.</p>
                    <hr>

                    {{-- MENU TRANSAKSI --}}
                    @canany(['karung.create_purchases', 'karung.view_purchases', 'karung.create_sales', 'karung.view_sales'])
                    <h5 class="mt-4">Menu Transaksi</h5>
                    <div class="list-group">
                        @canany(['karung.create_purchases', 'karung.view_purchases'])
                        <a href="{{ route('karung.purchases.index') }}" class="list-group-item list-group-item-action fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus-fill me-2" viewBox="0 0 16 16">
                                <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
                            </svg>
                            Manajemen Pembelian
                        </a>
                        @endcanany
                        @canany(['karung.create_sales', 'karung.view_sales'])
                        <a href="{{ route('karung.sales.index') }}" class="list-group-item list-group-item-action fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-receipt-cutoff me-2" viewBox="0 0 16 16">
                                <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m11.5-1.5a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-1 0v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1z"/>
                                <path d="M1.57 2.134a.5.5 0 0 1 0-.268l7.5-2.5a.5.5 0 0 1 .46 0l7.5 2.5a.5.5 0 0 1 0 .268l-7.5 2.5a.5.5 0 0 1-.46 0zM1 3.25a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5zM15 4V3.134a.5.5 0 0 1-.28-.432l-7.5-2.5a.5.5 0 0 0-.44 0l-7.5 2.5A.5.5 0 0 1 1 3.134V4h14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-14a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                            Manajemen Penjualan
                        </a>
                        @endcanany
                    </div>
                    @endcanany

                    {{-- MENU LAPORAN --}}
                    @can('karung.view_reports')
                    <h5 class="mt-4">Menu Laporan</h5>
                    <div class="list-group">
                        <a href="{{ route('karung.reports.sales') }}" class="list-group-item list-group-item-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-bar-graph-fill me-2" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M10 9a1 1 0 1 1 2 0v2a1 1 0 1 1-2 0zm-3 1a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm-3-3a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0z"/></svg>
                            Laporan Penjualan
                        </a>
                        <a href="{{ route('karung.reports.purchases') }}" class="list-group-item list-group-item-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-spreadsheet-fill me-2" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M5.884 4.68 8 7.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 8l2.233 2.68a.5.5 0 0 1-.768.64L8 8.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 8 5.116 5.32a.5.5 0 1 1 .768-.64"/></svg>
                            Laporan Pembelian
                        </a>
                        <a href="{{ route('karung.reports.stock') }}" class="list-group-item list-group-item-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-table me-2" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm-5 0H6v3h4zm-5 0H1v3h4zm10 4H1v3h14zm-5 0H6v3h4zm-5 0H1v3h4z"/></svg>
                            Laporan Stok
                        </a>
                        <a href="{{ route('karung.reports.profit_and_loss') }}" class="list-group-item list-group-item-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up-arrow me-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5"/></svg>
                            Laporan Laba Rugi
                        </a>
                    </div>
                    @endcan

                    {{-- MENU MASTER DATA --}}
                    @canany(['karung.manage_products', 'karung.manage_categories', 'karung.manage_types', 'karung.manage_suppliers', 'karung.manage_customers'])
                    <h5 class="mt-4">Menu Master Data</h5>
                    <div class="list-group">
                        @can('karung.manage_products')
                        <a href="{{ route('karung.products.index') }}" class="list-group-item list-group-item-action">
                            ⭐ Manajemen Produk Utama
                        </a>
                        @endcan
                        @can('karung.manage_categories')
                        <a href="{{ route('karung.product-categories.index') }}" class="list-group-item list-group-item-action">
                            Manajemen Kategori Produk
                        </a>
                        @endcan
                        @can('karung.manage_types')
                        <a href="{{ route('karung.product-types.index') }}" class="list-group-item list-group-item-action">
                            Manajemen Jenis Produk
                        </a>
                        @endcan
                        @can('karung.manage_suppliers')
                        <a href="{{ route('karung.suppliers.index') }}" class="list-group-item list-group-item-action">
                            Manajemen Supplier
                        </a>
                        @endcan
                        @can('karung.manage_customers')
                        <a href="{{ route('karung.customers.index') }}" class="list-group-item list-group-item-action">
                            Manajemen Pelanggan
                        </a>
                        @endcan
                    </div>
                    @endcanany
                </div>
            </div>
        </div>
    </div>
</div>
@endsection