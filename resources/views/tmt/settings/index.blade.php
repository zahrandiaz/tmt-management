@extends('layouts.tmt_app')

@section('title', 'Pengaturan Aplikasi - TMT Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Pengaturan Aplikasi</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('tmt.admin.settings.update') }}" method="POST">
                        @csrf
                        <p class="text-muted">Pengaturan ini berlaku untuk Instansi Bisnis yang sedang aktif.</p>
                        <hr>

                        {{-- [BARU] Pengaturan Informasi Toko --}}
                        <h5 class="mt-4 mb-3">Informasi Toko (untuk Struk)</h5>
                        <div class="mb-3">
                            <label for="store_name" class="form-label">Nama Toko</label>
                            <input type="text" class="form-control @error('store_name') is-invalid @enderror" id="store_name" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? '') }}">
                            @error('store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="store_address" class="form-label">Alamat Toko</label>
                            <textarea class="form-control @error('store_address') is-invalid @enderror" id="store_address" name="store_address" rows="3">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
                            @error('store_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="store_phone" class="form-label">No. Telepon Toko</label>
                            <input type="text" class="form-control @error('store_phone') is-invalid @enderror" id="store_phone" name="store_phone" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}">
                            @error('store_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <hr>
                        {{-- Akhir Blok Informasi Toko --}}


                        {{-- Pengaturan Stok Otomatis --}}
                        <h5 class="mt-4 mb-3">Pengaturan Modul</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="automatic_stock_management" name="automatic_stock_management" value="true" 
                                    {{ ($settings['automatic_stock_management'] ?? 'false') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="automatic_stock_management">
                                    <strong>Aktifkan Manajemen Stok Otomatis</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Jika diaktifkan, stok produk akan otomatis bertambah saat ada transaksi pembelian dan berkurang saat ada transaksi penjualan.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection