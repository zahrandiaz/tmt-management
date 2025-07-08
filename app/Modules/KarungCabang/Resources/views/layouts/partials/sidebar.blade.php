<div class="d-flex flex-column flex-shrink-0 p-3 h-100">
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        {{-- Item Menu Dashboard --}}
        <x-sidebar-nav-item :route="route('karungcabang.dashboard')" :active-routes="['karungcabang.dashboard']">
            <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#home"/></svg>
            Dashboard
        </x-sidebar-nav-item>

        {{-- Item Menu Produk --}}
        <x-sidebar-nav-item :route="route('karungcabang.products.index')" :active-routes="['karungcabang.products.*']" permission="karung.manage_products">
            <svg class="sidebar-icon me-2" width="16" height="16"><use xlink:href="#table"/></svg>
            Master Produk
        </x-sidebar-nav-item>
    </ul>
    <hr>
</div>