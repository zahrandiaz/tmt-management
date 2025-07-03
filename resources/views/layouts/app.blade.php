<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        {{-- [FIX] Menambahkan CSS untuk Tom Select & Bootstrap Icons --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">

        {{-- [FIX] Menambahkan Alpine.js. 'defer' penting agar dieksekusi setelah HTML selesai di-parse --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

        {{-- Menambahkan SweetAlert2 & Chart.js yang mungkin dibutuhkan di halaman lain --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        {{-- Hapus @vite jika tidak menggunakan build process --}}
    </head>
    <body class="d-flex flex-column h-100 bg-light">
        <div class="d-flex flex-column flex-grow-1">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white shadow-sm">
                    <div class="container-fluid py-3 px-4">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-grow-1">
                {{ $slot }}
            </main>
        </div>

        {{-- Pindahkan semua JS ke bagian bawah untuk performa yang lebih baik --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        
        {{-- [FIX] Menambahkan JavaScript untuk Tom Select --}}
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        
        {{-- Slot untuk script khusus halaman --}}
        @if (isset($scripts))
            {{ $scripts }}
        @endif
    </body>
</html>