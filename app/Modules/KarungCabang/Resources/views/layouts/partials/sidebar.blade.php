<div class="d-flex flex-column flex-shrink-0 p-3 h-100">
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        {{-- Item Menu Dashboard --}}
        <x-sidebar-nav-item :route="route('karungcabang.dashboard')" :active-routes="['karungcabang.dashboard']">
            <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#home"/></svg>
            Dashboard
        </x-sidebar-nav-item>

        {{-- (BARU) Dropdown Menu Transaksi --}}
        <x-sidebar-dropdown-menu
            permission="karung.access_module" {{-- Sesuaikan permission jika perlu --}}
            title="Transaksi"
            id="transaction-submenu"
            :active-routes="['karungcabang.sales.*', 'karungcabang.returns.*']"
        >
            <x-slot name="icon"><svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#grid"/></svg></x-slot>

            {{-- Link ke Transaksi Penjualan --}}
            @canany(['karung.view_sales', 'karung.create_sales'])
                <li><a href="{{ route('karungcabang.sales.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karungcabang.sales.*') ? 'active' : '' }}">Penjualan</a></li>
            @endcanany

            {{-- Link ke Riwayat Retur --}}
            @can('karung.manage_returns')
                <li><a href="{{ route('karungcabang.returns.sales.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karungcabang.returns.sales.*') ? 'active' : '' }}">Riwayat Retur</a></li>
            @endcan
        </x-sidebar-dropdown-menu>


        {{-- Item Menu Produk --}}
        <x-sidebar-nav-item :route="route('karungcabang.products.index')" :active-routes="['karungcabang.products.*']" permission="karung.manage_products">
            <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#table"/></svg>
            Master Produk
        </x-sidebar-nav-item>

        {{-- Dropdown Menu Master Lainnya (Link ke Modul Pusat) --}}
        <x-sidebar-dropdown-menu
            permission="karung.manage_categories"
            title="Master Lainnya"
            id="masterdata-submenu"
            :active-routes="['karung.product-categories.*', 'karung.product-types.*', 'karung.suppliers.*', 'karung.customers.*']"
        >
            <x-slot name="icon">
                <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
            </x-slot>

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
        </x-sidebar-dropdown-menu>
    </ul>
    <hr>
</div>