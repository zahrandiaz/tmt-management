{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Update Harga Beli Massal
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karungcabang::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Update Harga Beli Massal</h5>
                            <a href="{{ route('karungcabang.products.index') }}" class="btn btn-dark btn-sm">
                                <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Daftar Produk
                            </a>
                        </div>
                        <div class="card-body">
                            <x-flash-message />

                            <form action="{{ route('karungcabang.products.bulk-price.edit') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label for="search" class="form-label">Cari Nama / SKU</label>
                                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label for="category_id" class="form-label">Filter per Kategori</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Semua Kategori</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                                    </div>
                                </div>
                            </form>

                            <form action="{{ route('karungcabang.products.bulk-price.update') }}" method="POST">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>SKU</th>
                                                <th>Nama Produk</th>
                                                <th class="text-end">Harga Beli Lama (Rp)</th>
                                                <th style="width: 25%;">Harga Beli Baru (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($products as $product)
                                                <tr>
                                                    <td>{{ $product->sku }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td class="text-end">{{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                                                    <td>
                                                        <input type="number" step="any"
                                                               name="products[{{ $product->id }}]"
                                                               value="{{ old('products.' . $product->id, $product->purchase_price) }}"
                                                               class="form-control form-control-sm text-end @error('products.'.$product->id) is-invalid @enderror">
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">Produk tidak ditemukan. Coba gunakan filter lain.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($products->isNotEmpty())
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Anda yakin ingin menyimpan semua perubahan harga beli ini?')">
                                            <i class="bi bi-save-fill"></i> Simpan Semua Perubahan
                                        </button>
                                    </div>
                                @endif
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>