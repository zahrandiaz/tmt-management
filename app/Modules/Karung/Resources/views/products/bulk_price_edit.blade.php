@extends('karung::layouts.karung_app')

@section('title', 'Update Harga Beli Massal - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Update Harga Beli Massal</h5>
                    <a href="{{ route('karung.products.index') }}" class="btn btn-dark btn-sm">
                        &larr; Kembali ke Daftar Produk
                    </a>
                </div>
                <div class="card-body">
                    @include('karung::components.flash-message')

                    <form action="{{ route('karung.products.bulk-price.edit') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
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
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('karung.products.bulk-price.update') }}" method="POST">
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v6h-2a.5.5 0 0 0-.354.854l2.5 2.5a.5.5 0 0 0 .708 0l2.5-2.5A.5.5 0 0 0 10.5 7.5h-2z"/></svg>
                                    Simpan Semua Perubahan
                                </button>
                            </div>
                        @endif
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection