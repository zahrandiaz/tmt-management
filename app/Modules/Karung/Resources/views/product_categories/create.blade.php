{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>

    {{-- Mengisi slot 'header' di layout utama dengan judul halaman --}}
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Tambah Kategori Produk Baru
        </h2>
    </x-slot>

    {{-- Memanggil komponen layout modul (sidebar + content) --}}
    <x-module-layout>
        
        {{-- Mengisi slot 'sidebar' dengan file partial sidebar modul --}}
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Formulir Tambah Kategori</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('karung.product-categories.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- Tombol Aksi --}}
                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('karung.product-categories.index') }}" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle-fill"></i> Simpan Kategori
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </x-module-layout>
</x-app-layout>