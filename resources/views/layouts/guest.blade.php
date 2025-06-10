<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center pt-6 sm:pt-0 bg-light">
            <div>
                <a href="/">
                    <h2 class="text-dark">{{ config('app.name', 'Laravel') }}</h2>
                </a>
            </div>

            <div class="w-100 mt-4 px-4 py-4 bg-white shadow-sm overflow-hidden rounded-lg" style="max-width: 450px;">
                {{-- Di sini konten dari halaman login/register akan disisipkan --}}
                @yield('content')
            </div>
        </div>
    </body>
</html>