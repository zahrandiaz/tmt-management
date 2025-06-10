@extends('layouts.tmt_app')

@section('title', 'Laporan Stok Produk - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Stok Produk</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                    {{-- Nanti bisa tambah tombol Export PDF/Excel di sini --}}
                </div>
                <div class="card-body">
                    {{-- Nanti kita bisa tambahkan form filter di sini (berdasarkan kategori, jenis, dll) --}}

                    <div class="alert alert-info">
                        <strong>Info:</strong> Laporan ini menampilkan jumlah stok yang tercatat di master data produk (stok referensi/manual), bukan stok hasil kalkulasi transaksi.
                    </div>

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