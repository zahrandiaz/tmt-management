@extends('karung::layouts.karung_app')

@section('title', 'Laporan Stok Produk - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Stok Produk</h5>
                    <div>
                        <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">...</svg>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- [BARU] Form Filter --}}
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
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4">
                        <strong>Export Laporan:</strong>
                        <a href="{{ route('karung.reports.stock.export', request()->query()) }}" class="btn btn-success btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16">...</svg>
                            Excel
                        </a>
                        <a href="{{ route('karung.reports.stock.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf-fill" viewBox="0 0 16 16">...</svg>
                            PDF
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
                                    <tr class="{{ $product->stock <= $product->min_stock_level ? 'table-danger' : '' }}">
                                        <th scope="row">{{ $products->firstItem() + $index }}</th>
                                        <td>{{ $product->sku }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category?->name ?: '-' }}</td>
                                        <td>{{ $product->type?->name ?: '-' }}</td>
                                        <td class="text-center">{{ $product->min_stock_level }}</td>
                                        <td class="text-center fw-bold">{{ $product->stock }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('karung.reports.stock.history', $product->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Riwayat Stok">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                                    <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.798a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.986 7.986 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zM12 8.5a.5.5 0 0 1 .5-.5h.5a.5.5 0 0 1 0 1h-.5a.5.5 0 0 1-.5-.5m-.002-4.205a7.002 7.002 0 0 0-.299-.985l-.976.219a6.996 6.996 0 0 0 .27.44l.933-.364zM8.5 7.999a.5.5 0 0 1 .5-.5h2.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/><path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m15 0a7 7 0 1 0-14 0 7 7 0 0 0 14 0"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-3">
                        {{ $products->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection