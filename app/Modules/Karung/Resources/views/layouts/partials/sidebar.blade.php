<div class="d-flex flex-column flex-shrink-0 p-3 h-100" x-data="notificationHandler()" x-init="startPolling()">
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        {{-- Dropdown Notifikasi --}}
        @auth
        <li class="nav-item dropdown">
            <a href="#" class="nav-link text-white dropdown-toggle position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                <svg class="sidebar-icon me-2" width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/></svg>
                Notifikasi
                <template x-if="notificationCount > 0">
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;" x-text="notificationCount"></span>
                </template>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" style="width: 320px;">
                <template x-if="notifications.length > 0">
                    <template x-for="(notification, index) in notifications.slice(0, 5)" :key="notification.id">
                        <li>
                            <div class="dropdown-item-text text-white-50 p-2 border-bottom border-secondary">
                                <p class="mb-1 small" x-text="notification.data.message"></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a :href="notification.data.download_url" class="fw-bold text-white" x-text="'Download: ' + notification.data.file_name.substring(0, 20) + '...'"></a>
                                    <button @click="markAsRead(notification, index)" class="btn btn-sm btn-outline-light" title="Tandai sudah dibaca">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>
                                    </button>
                                </div>
                                <div class="text-xs text-white-50 opacity-75 mt-1" x-text="new Date(notification.created_at).toLocaleString('id-ID')"></div>
                            </div>
                        </li>
                    </template>
                </template>
                <template x-if="notifications.length == 0">
                    <li><span class="dropdown-item-text text-white-50">Tidak ada notifikasi baru.</span></li>
                </template>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center" href="{{ route('karung.reports.download_center') }}">Lihat Semua Riwayat</a></li>
            </ul>
        </li>
        <hr>
        @endauth

        {{-- Item Menu Dashboard --}}
        <x-sidebar-nav-item :route="route('karung.dashboard')" :active-routes="['karung.dashboard']">
            <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#home"/></svg>
            Dashboard Modul
        </x-sidebar-nav-item>

        {{-- Dropdown Menu Transaksi --}}
        <x-sidebar-dropdown-menu permission="karung.view_purchases" title="Transaksi" id="transaction-submenu" :active-routes="['karung.purchases.*', 'karung.sales.*', 'karung.returns.*']">
            <x-slot name="icon"><svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#grid"/></svg></x-slot>
            {{-- Isi dropdown --}}
            @canany(['karung.view_purchases', 'karung.create_purchases'])
                <li><a href="{{ route('karung.purchases.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.purchases.*') ? 'active' : '' }}">Pembelian</a></li>
            @endcanany
            @canany(['karung.view_sales', 'karung.create_sales'])
                <li><a href="{{ route('karung.sales.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.sales.*') ? 'active' : '' }}">Penjualan</a></li>
            @endcanany
            @can('karung.manage_returns')
                <li><hr class="dropdown-divider bg-light"></li>
                <li>
                    <a href="#return-submenu" data-bs-toggle="collapse" class="nav-link text-white {{ request()->is('*returns*') ? '' : 'collapsed' }}">Riwayat Retur</a>
                    <div class="collapse {{ request()->is('*returns*') ? 'show' : '' }}" id="return-submenu">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
                            <li><a href="{{ route('karung.returns.sales.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.returns.sales.*') ? 'active' : '' }}">- Retur Penjualan</a></li>
                            <li><a href="{{ route('karung.returns.purchases.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.returns.purchases.*') ? 'active' : '' }}">- Retur Pembelian</a></li>
                        </ul>
                    </div>
                </li>
            @endcan
        </x-sidebar-dropdown-menu>

        {{-- Dropdown Menu Inventaris --}}
        <x-sidebar-dropdown-menu permission="karung.manage_products" title="Inventaris" id="inventory-submenu" :active-routes="['karung.products.*', 'karung.stock-adjustments.*']">
            <x-slot name="icon"><svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#table"/></svg></x-slot>
            {{-- Isi dropdown --}}
            @can('karung.manage_products')
                <li><a href="{{ route('karung.products.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.products.*') ? 'active' : '' }}">Master Produk</a></li>
            @endcan
            @can('karung.manage_stock_adjustments')
                <li><a href="{{ route('karung.stock-adjustments.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.stock-adjustments.*') ? 'active' : '' }}">Penyesuaian Stok</a></li>
            @endcan
        </x-sidebar-dropdown-menu>

        {{-- Dropdown Menu Keuangan --}}
        <x-sidebar-dropdown-menu permission="karung.view_reports" title="Keuangan" id="finance-submenu" :active-routes="['karung.reports.*', 'karung.operational-expenses.*', 'karung.financials.*']">
            <x-slot name="icon"><svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#cash-coin"/></svg></x-slot>
            {{-- Isi dropdown --}}
            @can('karung.manage_payments')
                <li><a href="{{ route('karung.financials.receivables') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.financials.receivables') ? 'active' : '' }}">Manajemen Piutang</a></li>
                <li><a href="{{ route('karung.financials.payables') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.financials.payables') ? 'active' : '' }}">Manajemen Utang</a></li>
                <li><hr class="dropdown-divider bg-light"></li>
            @endcan
            @can('karung.manage_expenses')
                <li><a href="{{ route('karung.operational-expenses.index') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.operational-expenses.*') ? 'active' : '' }}">Biaya Operasional</a></li>
            @endcan
            @can('karung.view_reports')
                <li><hr class="dropdown-divider bg-light"></li>
                <li><a href="{{ route('karung.reports.download_center') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.download_center') ? 'active' : '' }}">Pusat Unduhan</a></li>
                <li><a href="{{ route('karung.reports.sales') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.sales') ? 'active' : '' }}">Laporan Penjualan</a></li>
                <li><a href="{{ route('karung.reports.purchases') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.purchases') ? 'active' : '' }}">Laporan Pembelian</a></li>
                <li><a href="{{ route('karung.reports.stock') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.stock*') ? 'active' : '' }}">Laporan Stok</a></li>
                <li><a href="{{ route('karung.reports.profit_and_loss') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.profit_and_loss') ? 'active' : '' }}">Laporan Laba Rugi</a></li>
                <li><a href="{{ route('karung.reports.cash_flow') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.cash_flow') ? 'active' : '' }}">Laporan Arus Kas</a></li>
                <li><hr class="dropdown-divider bg-light"></li>
                <li><a href="{{ route('karung.reports.customer_performance') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.customer_performance') ? 'active' : '' }}">Performa Pelanggan</a></li>
                <li><a href="{{ route('karung.reports.product_performance') }}" class="nav-link text-white rounded {{ request()->routeIs('karung.reports.product_performance') ? 'active' : '' }}">Performa Produk</a></li>
            @endcan
        </x-sidebar-dropdown-menu>

        {{-- Dropdown Menu Master Lainnya --}}
        <x-sidebar-dropdown-menu permission="karung.manage_categories" title="Master Lainnya" id="masterdata-submenu" :active-routes="['karung.product-categories.*', 'karung.product-types.*', 'karung.suppliers.*', 'karung.customers.*']">
            <x-slot name="icon"><svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg></x-slot>
            {{-- Isi dropdown --}}
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
    {{-- Dropdown User --}}
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

{{-- Script Notifikasi --}}
<script>
    function notificationHandler() {
        return {
            notifications: @json(auth()->user()->unreadNotifications),
            notificationCount: {{ auth()->user()->unreadNotifications->count() }},
            
            startPolling() {
                setInterval(() => {
                    this.fetchLatestNotifications();
                }, 15000);
            },

            fetchLatestNotifications() {
                fetch('{{ route('karung.notifications.latest') }}')
                    .then(response => response.json())
                    .then(data => {
                        this.notifications = data;
                        this.notificationCount = data.length;
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            },

            markAsRead(notification, index) {
                const urlTemplate = '{{ route('karung.notification.markAsRead', ['notificationId' => 'NOTIFICATION_ID']) }}';
                const finalUrl = urlTemplate.replace('NOTIFICATION_ID', notification.id);
                
                fetch(finalUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        this.fetchLatestNotifications();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    }
</script>