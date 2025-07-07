@props([
    'title',
    'backUrl',
    'backText',
    'description',
])

<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>
        <a href="{{ $backUrl }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left-circle-fill"></i>
            {{ $backText }}
        </a>
    </div>
    <div class="card-body">
        <p>{{ $description }}</p>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    {{-- Slot untuk header tabel --}}
                    {{ $headers }}
                </thead>
                <tbody>
                    {{-- Slot utama untuk seluruh isi tabel (baris-baris) --}}
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
</div>