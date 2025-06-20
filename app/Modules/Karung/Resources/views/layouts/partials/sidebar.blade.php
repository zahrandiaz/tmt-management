<div class="d-flex flex-column flex-shrink-0 p-3 h-100">
    {{-- ... (Header sidebar tidak berubah) ... --}}
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('karung.dashboard') }}" class="nav-link text-white {{ request()->routeIs('karung.dashboard') ? 'active' : '' }}">
                <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#home"/></svg>
                Dashboard Modul
            </a>
        </li>
        
        @canany(['karung.view_purchases', 'karung.create_purchases', 'karung.view_sales', 'karung.create_sales'])
        <li>
            <a href="#transaction-submenu" data-bs-toggle="collapse" class="nav-link text-white {{ request()->routeIs('karung.purchases.*') || request()->routeIs('karung.sales.*') ? '' : 'collapsed' }}">
                <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
                Transaksi
            </a>
            <div class="collapse {{ request()->routeIs('karung.purchases.*') || request()->routeIs('karung.sales.*') ? 'show' : '' }}" id="transaction-submenu">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                    @canany(['karung.view_purchases', 'karung.create_purchases'])
                    <li><a href="{{ route('karung.purchases.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.purchases.*') ? 'active' : '' }}">Pembelian</a></li>
                    @endcanany
                    @canany(['karung.view_sales', 'karung.create_sales'])
                    <li><a href="{{ route('karung.sales.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.sales.*') ? 'active' : '' }}">Penjualan</a></li>
                    @endcanany
                </ul>
            </div>
        </li>
        @endcanany

        {{-- [BARU] Grup Menu Inventaris --}}
        @canany(['karung.manage_products', 'karung.manage_stock_adjustments'])
        <li>
            <a href="#inventory-submenu" data-bs-toggle="collapse" class="nav-link text-white {{ request()->is('*products*') || request()->is('*stock-adjustments*') ? '' : 'collapsed' }}">
                <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#table"/></svg>
                Inventaris
            </a>
            <div class="collapse {{ request()->is('*products*') || request()->is('*stock-adjustments*') ? 'show' : '' }}" id="inventory-submenu">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                    @can('karung.manage_products')
                    <li><a href="{{ route('karung.products.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.products.*') ? 'active' : '' }}">Master Produk</a></li>
                    @endcan
                    @can('karung.manage_stock_adjustments')
                    <li><a href="{{ route('karung.stock-adjustments.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.stock-adjustments.*') ? 'active' : '' }}">Penyesuaian Stok</a></li>
                    @endcan
                </ul>
            </div>
        </li>
        @endcanany

        @canany(['karung.view_reports', 'karung.manage_expenses'])
        <li>
            <a href="#finance-submenu" data-bs-toggle="collapse" class="nav-link text-white {{ request()->is('*reports*') || request()->is('*operational-expenses*') ? '' : 'collapsed' }}">
                <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#cash-coin"/></svg>
                Keuangan
            </a>
            <div class="collapse {{ request()->is('*reports*') || request()->is('*operational-expenses*') ? 'show' : '' }}" id="finance-submenu">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                    @can('karung.manage_expenses')
                    <li><a href="{{ route('karung.operational-expenses.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.operational-expenses.*') ? 'active' : '' }}">Biaya Operasional</a></li>
                    @endcan
                    @can('karung.view_reports')
                    <li><hr class="dropdown-divider bg-light"></li>
                    <li><a href="{{ route('karung.reports.sales') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.sales') ? 'active' : '' }}">Laporan Penjualan</a></li>
                    <li><a href="{{ route('karung.reports.purchases') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.purchases') ? 'active' : '' }}">Laporan Pembelian</a></li>
                    
                    {{-- [PERBAIKAN] Logika '||' dihapus dari href dan hanya diletakkan di class --}}
                    <li><a href="{{ route('karung.reports.stock') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.stock*') ? 'active' : '' }}">Laporan Stok</a></li>
                    
                    <li><a href="{{ route('karung.reports.profit_and_loss') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.profit_and_loss') ? 'active' : '' }}">Laporan Laba Rugi</a></li>
                    <li><a href="{{ route('karung.reports.cash_flow') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.cash_flow') ? 'active' : '' }}">Laporan Arus Kas</a></li>
                    <li><hr class="dropdown-divider bg-light"></li>
                    <li><a href="{{ route('karung.reports.customer_performance') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.customer_performance') ? 'active' : '' }}">Performa Pelanggan</a></li>
                    <li><a href="{{ route('karung.reports.product_performance') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.product_performance') ? 'active' : '' }}">Performa Produk</a></li>
                    @endcan
                </ul>
            </div>
        </li>
        @endcanany
        
        @canany(['karung.manage_categories', 'karung.manage_types', 'karung.manage_suppliers', 'karung.manage_customers'])
        <li>
            {{-- ... (Submenu Master Data direstrukturisasi, Produk pindah ke Inventaris) ... --}}
            <a href="#masterdata-submenu" data-bs-toggle="collapse" class="nav-link text-white {{ request()->is('*product-categories*','*product-types*','*suppliers*','*customers*') ? '' : 'collapsed' }}">
                <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                Master Lainnya
            </a>
            <div class="collapse {{ request()->is('*product-categories*','*product-types*','*suppliers*','*customers*') ? 'show' : '' }}" id="masterdata-submenu">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                    @can('karung.manage_categories')
                    <li><a href="{{ route('karung.product-categories.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.product-categories.*') ? 'active' : '' }}">Kategori</a></li>
                    @endcan
                    @can('karung.manage_types')
                    <li><a href="{{ route('karung.product-types.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.product-types.*') ? 'active' : '' }}">Jenis</a></li>
                    @endcan
                    @can('karung.manage_suppliers')
                    <li><a href="{{ route('karung.suppliers.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.suppliers.*') ? 'active' : '' }}">Supplier</a></li>
                    @endcan
                    @can('karung.manage_customers')
                    <li><a href="{{ route('karung.customers.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.customers.*') ? 'active' : '' }}">Pelanggan</a></li>
                    @endcan
                </ul>
            </div>
        </li>
        @endcanany
    </ul>
    <hr>
    <div class="dropdown mt-auto">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong>{{ Auth::user()->name }}</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            @role('Super Admin TMT')
            <li><a class="dropdown-item" href="{{ route('tmt.admin.settings.index') }}">Pengaturan</a></li>
            @endrole
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    Sign out
                </a>
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</div>