{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>

    {{-- Mengisi slot 'header' di layout utama dengan judul halaman --}}
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Edit Produk: {{ $product->name }}
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
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Formulir Edit Produk: {{ $product->name }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('karung.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- Baris 1: Nama Produk & Kategori --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="product_category_id" class="form-label">Kategori Produk</label>
                                        <select class="form-select @error('product_category_id') is-invalid @enderror" id="product_category_id" name="product_category_id">
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Baris 2: Jenis Produk & Supplier Langganan --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="product_type_id" class="form-label">Jenis Produk</label>
                                        <select class="form-select @error('product_type_id') is-invalid @enderror" id="product_type_id" name="product_type_id">
                                            <option value="">-- Pilih Jenis --</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}" {{ old('product_type_id', $product->product_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="default_supplier_id" class="form-label">Supplier Langganan (Opsional)</label>
                                        <select class="form-select @error('default_supplier_id') is-invalid @enderror" id="default_supplier_id" name="default_supplier_id">
                                            <option value="">-- Pilih Supplier --</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('default_supplier_id', $product->default_supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('default_supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Baris 3: Harga Beli & Harga Jual --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="purchase_price" class="form-label">Harga Beli (Referensi)</label>
                                        <input type="number" step="any" class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}">
                                        @error('purchase_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="selling_price" class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                        <input type="number" step="any" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required>
                                        @error('selling_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Baris 4: Stok & Stok Minimal --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="stock" class="form-label">Stok (Referensi)</label>
                                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}">
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="min_stock_level" class="form-label">Stok Minimal</label>
                                        <input type="number" class="form-control @error('min_stock_level') is-invalid @enderror" id="min_stock_level" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level) }}">
                                        @error('min_stock_level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Deskripsi --}}
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi/Spesifikasi Lain</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Upload Gambar --}}
                                <div class="mb-3">
                                    <label for="image_path" class="form-label">Ganti Foto Produk (Opsional)</label>
                                    <div class="mb-2">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="img-thumbnail" width="150">
                                        @else
                                            <p class="text-muted small">Tidak ada foto saat ini.</p>
                                        @endif
                                    </div>
                                    <input class="form-control @error('image_path') is-invalid @enderror" type="file" id="image_path" name="image_path">
                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
                                    @error('image_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Status Aktif --}}
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Produk Aktif (Bisa Dijual)</label>
                                </div>

                                {{-- Tombol Aksi --}}
                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('karung.products.index') }}" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-save-fill"></i> Simpan Perubahan
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