{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Stok Produk
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Laporan Stok Produk</h5>
                            <div>
                                <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Form Filter --}}
                            <form method="GET" action="{{ route('karung.reports.stock') }}" class="mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label for="category_id" class="form-label">Filter Berdasarkan Kategori</label>
                                        <select name="category_id" id="category_id" class="form-select">
                                            <option value="">Semua Kategori</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter</button>
                                        <a href="{{ route('karung.reports.stock') }}" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i></a>
                                    </div>
                                </div>
                            </form>

                            {{-- Tombol Export --}}
                            <div class="mb-4">
                                <strong>Export Laporan:</strong>
                                <a href="{{ route('karung.reports.stock.export', request()->query()) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                                </a>
                                <a href="{{ route('karung.reports.stock.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                </a>
                            </div>

                            <p>Halaman ini menampilkan daftar semua produk beserta jumlah stok, harga, dan nilai inventaris saat ini.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="width: 5%;">No.</th>
                                            <th scope="col">SKU</th>
                                            <th scope="col">Nama Produk</th>
                                            <th scope="col">Kategori</th>
                                            <th scope="col">Jenis</th>
                                            <th scope="col" class="text-center">Stok Minimal</th>
                                            <th scope="col" class="text-center fw-bold">Stok Saat Ini (Ref.)</th>
                                            <th scope="col" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($products as $index => $product)
                                            <tr class="{{ $product->stock <= $product->min_stock_level && $product->min_stock_level > 0 ? 'table-danger' : '' }}">
                                                <th scope="row">{{ $products->firstItem() + $index }}</th>
                                                <td>{{ $product->sku }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->category?->name ?: '-' }}</td>
                                                <td>{{ $product->type?->name ?: '-' }}</td>
                                                <td class="text-center">{{ $product->min_stock_level }}</td>
                                                <td class="text-center fw-bold">{{ $product->stock }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('karung.reports.stock.history', $product->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Riwayat Stok">
                                                        <i class="bi bi-clock-history"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data produk.</td>
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