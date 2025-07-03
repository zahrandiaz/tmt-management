{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>

    {{-- Mengisi slot 'header' di layout utama dengan judul halaman --}}
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Daftar Jenis Produk
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
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Jenis Produk</h5>
                            <div>
                                <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                                </a>
                                @can('karung.manage_types')
                                <a href="{{ route('karung.product-types.create') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Jenis Baru
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- Form Pencarian --}}
                            <div class="mb-4">
                                <form action="{{ route('karung.product-types.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan nama jenis..." value="{{ request('search') }}">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search"></i> Cari
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Tabel Data --}}
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="width: 5%;">No.</th>
                                            <th scope="col">Nama Jenis</th>
                                            <th scope="col" style="width: 15%;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($types as $index => $type)
                                            <tr>
                                                <th scope="row">{{ $types->firstItem() + $index }}</th>
                                                <td>{{ $type->name }}</td>
                                                <td class="text-center">
                                                    @can('karung.manage_types')
                                                    <a href="{{ route('karung.product-types.edit', $type->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form action="{{ route('karung.product-types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis produk ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Tidak ada data jenis produk.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Link Paginasi --}}
                            <div class="mt-3">
                                {{ $types->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </x-module-layout>
</x-app-layout>