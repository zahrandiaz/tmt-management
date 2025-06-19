<!doctype html>
{{-- [PERBAIKAN] Tambahkan class d-flex flex-column untuk struktur dasar yang benar --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100 d-flex flex-column">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* [PERBAIKAN] Sederhanakan CSS, andalkan class Bootstrap */
        body { height: 100%; }
        .sidebar-icon { fill: currentColor; }
        @media print {
            body > #tmt_app > nav, .offcanvas, footer, .no-print { display: none !important; }
            body, main, .content-wrapper, .card, .card-body { background-color: white !important; padding: 0 !important; margin: 0 !important; border: none !important; box-shadow: none !important; }
            .card-header { text-align: center !important; background-color: transparent !important; color: black !important; border-bottom: 1px solid #ddd !important; padding-bottom: 10px !important; }
            .table { font-size: 11px; color: black !important; }
            .table-dark th { background-color: #eee !important; color: black !important; border-color: #ddd !important; }
        }
    </style>
    
    @stack('head-scripts')
</head>
{{-- [PERBAIKAN] Body juga harus menjadi flex container --}}
<body class="d-flex flex-column h-100">
    {{-- [PERBAIKAN] div#tmt_app sekarang menjadi flex-grow-1 --}}
    <div id="tmt_app" class="d-flex flex-column flex-grow-1">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                @if(request()->is('tmt/karung/*') || request()->is('tmt-admin/*'))
                <button class="btn btn-dark d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#moduleSidebar" aria-controls="moduleSidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/></svg>
                </button>
                @endif
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    {{-- Menu items tidak berubah --}}
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard TMT</a></li>
                            @role('Super Admin TMT')
                            <li class="nav-item"><a class="nav-link" href="{{ route('tmt.admin.users.index') }}">Pengguna</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('tmt.admin.roles.index') }}">Peran</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('tmt.admin.settings.index') }}">Pengaturan</a></li>
                            @can('view system logs')
                            <li class="nav-item"><a class="nav-link" href="{{ route('tmt.admin.activity_log.index') }}">Log Aktivitas</a></li>
                            @endcan
                            @endrole
                        @endauth
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))<li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>@endif
                            @if (Route::has('register'))<li class="nav-item"><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>@endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>{{ Auth::user()->name }}</a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">Profil Saya</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- [PERBAIKAN] Main sekarang menjadi container flex yang tumbuh dan menyembunyikan overflow --}}
        <main class="d-flex flex-grow-1" style="overflow: hidden;">
            @yield('content')
        </main>
    </div>
    
    {{-- Script tidak berubah --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('footer-scripts')
</body>
</html>