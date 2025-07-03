{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Edit Biaya Operasional
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Form Edit Biaya Operasional</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('karung.operational-expenses.update', $operationalExpense->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('karung::operational_expenses._form')
                    </form>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>