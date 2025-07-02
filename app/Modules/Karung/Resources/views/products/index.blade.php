{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>

    {{-- Mengisi slot 'header' di layout utama dengan judul halaman --}}
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Daftar Produk Utama
        </h2>
    </x-slot>

    {{-- Memanggil komponen layout modul (sidebar + content) --}}
    <x-module-layout>
        
        {{-- Mengisi slot 'sidebar' dengan file partial sidebar modul --}}
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        {{-- Semua konten asli dari file lama kita letakkan di sini --}}
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Produk Utama</h5>
                            <div>
                                <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle-fill"></i>
                                    Kembali
                                </a>
                                @can('karung.manage_products') {{-- Hak akses ditambahkan untuk konsistensi --}}
                                <a href="{{ route('karung.products.bulk-price.edit') }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-tags-fill"></i>
                                    Update Harga Massal
                                </a>
                                <a href="{{ route('karung.products.create') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Tambah Produk Baru
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
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
                                <form action="{{ route('karung.products.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan Nama Produk atau SKU..." value="{{ request('search') }}">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search"></i>
                                            Cari
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
                                            <th scope="col">SKU</th>
                                            <th scope="col">Nama Produk</th>
                                            <th scope="col">Kategori</th>
                                            <th scope="col">Jenis</th>
                                            <th scope="col" class="text-end">Harga Jual</th>
                                            <th scope="col" class="text-center">Stok (Ref.)</th>
                                            <th scope="col" class="text-center">Status</th>
                                            <th scope="col" style="width: 15%;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($products as $index => $product)
                                            <tr>
                                                <th scope="row">{{ $products->firstItem() + $index }}</th>
                                                <td>{{ $product->sku }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->category?->name ?: '-' }}</td>
                                                <td>{{ $product->type?->name ?: '-' }}</td>
                                                <td class="text-end">{{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                                <td class="text-center">{{ $product->stock }}</td>
                                                <td class="text-center">
                                                    @if ($product->is_active)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-danger">Tidak Aktif</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @can('update', $product)
                                                    <a href="{{ route('karung.products.edit', $product->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete', $product)
                                                    <form action="{{ route('karung.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
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
                                                <td colspan="9" class="text-center">Tidak ada data produk utama.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Link Paginasi --}}
                            <div class="mt-3">
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </x-module-layout>
</x-app-layout>