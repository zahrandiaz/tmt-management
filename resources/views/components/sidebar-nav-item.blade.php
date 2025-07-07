@props([
    'permission' => null, // Ubah default menjadi null
    'route',
    'activeRoutes' => []
])

@php
    $isActive = collect($activeRoutes)->contains(fn($pattern) => request()->routeIs($pattern));

    // Tampilkan item jika tidak ada permission yang di-set, ATAU jika user punya permission
    $showItem = is_null($permission) ? true : auth()->user()->can($permission);
@endphp

@if($showItem)
    <li class="nav-item">
        <a href="{{ $route }}" class="nav-link text-white {{ $isActive ? 'active' : '' }}">
            {{ $slot }}
        </a>
    </li>
@endif