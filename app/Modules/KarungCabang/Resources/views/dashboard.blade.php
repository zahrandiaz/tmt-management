<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Dashboard Modul Karung Cabang
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karungcabang::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Selamat Datang!</h5>
                    <p class="card-text">
                        Kerangka dasar modul baru Anda telah berhasil dibuat!
                    </p>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>