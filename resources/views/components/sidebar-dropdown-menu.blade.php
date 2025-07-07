@props([
    'permission', // Hak akses untuk melihat seluruh menu dropdown
    'title',      // Judul menu (e.g., "Transaksi")
    'id',         // ID unik untuk target collapse (e.g., "transaction-submenu")
    'activeRoutes' => [] // Array route patterns untuk menandai menu aktif
])

@php
    // Cek apakah ada route di dalam daftar activeRoutes yang sedang aktif
    $isActive = collect($activeRoutes)->contains(fn($pattern) => request()->is($pattern));
@endphp

@can($permission)
<li>
    <a href="#{{ $id }}" data-bs-toggle="collapse" class="nav-link text-white {{ $isActive ? '' : 'collapsed' }}">
        {{-- Slot untuk ikon bisa ditambahkan di sini jika perlu --}}
        {{ $icon ?? '' }}
        {{ $title }}
    </a>
    <div class="collapse {{ $isActive ? 'show' : '' }}" id="{{ $id }}">
        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
            {{-- Slot utama untuk diisi link-link di dalam dropdown --}}
            {{ $slot }}
        </ul>
    </div>
</li>
@endcan