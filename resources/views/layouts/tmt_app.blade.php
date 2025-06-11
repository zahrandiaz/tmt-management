<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- <link href="{{ asset('css/tmt_custom.css') }}" rel="stylesheet"> --}}

{{-- CSS Khusus untuk Print (Versi Ditingkatkan) --}}
<style>
    @media print {
        /* Sembunyikan elemen yang tidak perlu dicetak */
        body > #tmt_app > nav, 
        body > #tmt_app > footer,
        .card-header .btn, /* Tombol di card header */
        .no-print /* Class helper untuk elemen yang tidak ingin dicetak */
        { 
            display: none !important; 
        }

        /* Atur ulang layout utama untuk cetak */
        body {
            background-color: white !important; /* Paksa background jadi putih */
        }
        main.py-4 {
            padding: 0 !important;
            margin: 0 !important;
        }
        .container-fluid, .container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Atur ulang tampilan card agar seperti kertas biasa */
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .card-header {
            background-color: transparent !important;
            color: black !important;
            border-bottom: 1px solid #ddd !important;
            padding: 0 0 10px 0 !important;
            text-align: center; /* Judul struk di tengah */
        }
        .card-body {
            padding: 10px 0 0 0 !important;
        }

        /* Atur ulang tampilan tabel agar lebih mirip struk */
        .table {
            font-size: 12px; /* Perkecil font tabel */
            color: black !important;
        }
        .table-dark th {
            background-color: #f2f2f2 !important; /* Ganti header tabel jadi abu-abu muda */
            color: black !important;
            border-color: #ddd !important;
        }
    }
</style>

    @stack('head-scripts')
</head>
<body>
    <div id="tmt_app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                {{-- Mengarahkan ke route 'dashboard' yang sudah kita atur --}}
                                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard TMT</a>
                            </li>

                            {{-- === BLOK KODE BARU DIMULAI DI SINI === --}}
                            @role('Super Admin TMT')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('tmt.admin.users.index') }}">Manajemen Pengguna</a>
                            </li>
                            {{-- Di sini nanti bisa ditambahkan link untuk Manajemen Peran, dll. --}}
                            <li class="nav-item"> {{-- <-- TAMBAHKAN BLOK LI INI --}}
                                <a class="nav-link" href="{{ route('tmt.admin.roles.index') }}">Manajemen Peran</a>
                            </li>
                            <li class="nav-item"> {{-- <-- TAMBAHKAN BLOK LI INI --}}
                                <a class="nav-link" href="{{ route('tmt.admin.settings.index') }}">Pengaturan</a>
                            </li>
                            @endrole
                            {{-- === BLOK KODE BARU SELESAI DI SINI === --}}

                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    {{-- Mengarahkan ke halaman profil yang dibuat Breeze --}}
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        Profil Saya
                                     </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @yield('content')
            </div>
        </main>

        <footer class="py-4 mt-auto bg-light">
            <div class="container text-center">
                <small>Hak Cipta &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Semua Hak Dilindungi.</small>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    {{-- <script src="{{ asset('js/tmt_custom.js') }}"></script> --}}
    @stack('footer-scripts')
</body>
</html>