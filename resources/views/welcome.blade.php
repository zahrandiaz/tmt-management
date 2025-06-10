<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

        <!-- Custom Style -->
        <style>
            .hero-section {
                padding: 6rem 0;
                background-color: #f8f9fa;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="d-flex flex-column min-vh-100">
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    <div class="d-flex">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary btn-sm">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm me-2">Log in</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm">Register</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </nav>

            <main class="flex-grow-1">
                <div class="hero-section text-center">
                    <div class="container">
                        <h1 class="display-4 fw-bold">Selamat Datang di TMT Management Platform</h1>
                        <p class="lead text-muted col-md-8 mx-auto">Satu platform terpusat untuk mengelola semua unit bisnis Anda secara efisien dan terintegrasi.</p>
                        <div class="mt-4">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">Masuk ke Aplikasi</a>
                        </div>
                    </div>
                </div>
                
                {{-- Bagian Fitur (Opsional, tapi membuat tampilan lebih menarik) --}}
                <div class="container px-4 py-5">
                    <h2 class="pb-2 border-bottom text-center">Fitur Unggulan</h2>
                    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
                        <div class="col d-flex align-items-start">
                            <div class="text-bg-primary bg-gradient fs-4 rounded-3 me-3 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-collection-fill" viewBox="0 0 16 16">
                                    <path d="M0 13a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 16 13V6a1.5 1.5 0 0 0-1.5-1.5h-13A1.5 1.5 0 0 0 0 6z"/>
                                    <path d="M2 3a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 0-1h-11A.5.5 0 0 0 2 3m2-2a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7A.5.5 0 0 0 4 1"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="fs-5">Manajemen Modular</h3>
                                <p>Kelola berbagai unit bisnis, seperti Toko Karung, secara terpisah namun tetap dalam satu platform terintegrasi.</p>
                            </div>
                        </div>
                        <div class="col d-flex align-items-start">
                            <div class="text-bg-primary bg-gradient fs-4 rounded-3 me-3 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="fs-5">Kontrol Akses Pengguna</h3>
                                <p>Atur peran dan hak akses untuk setiap pengguna dengan detail, memastikan setiap orang hanya bisa mengakses fitur yang relevan.</p>
                            </div>
                        </div>
                        <div class="col d-flex align-items-start">
                            <div class="text-bg-primary bg-gradient fs-4 rounded-3 me-3 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-bar-graph-fill" viewBox="0 0 16 16">
                                    <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M10 9a1 1 0 1 1 2 0v2a1 1 0 1 1-2 0zm-3 1a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm-3-3a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="fs-5">Pelaporan Dinamis</h3>
                                <p>Dapatkan wawasan bisnis dengan laporan penjualan, pembelian, stok, hingga laba rugi yang mudah diakses dan dipahami.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <footer class="py-4 mt-auto bg-white border-top">
                <div class="container text-center">
                    <small class="text-muted">Hak Cipta &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Semua Hak Dilindungi.</small>
                </div>
            </footer>
        </div>

        <!-- Bootstrap JS Bundle CDN -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
