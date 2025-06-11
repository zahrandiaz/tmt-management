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

                        {{-- Pengaturan Stok Otomatis --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                {{-- Kita ambil nilai dari variabel $settings.
                                     $settings['automatic_stock_management'] ?? 'false' artinya jika kuncinya tidak ada, anggap nilainya 'false'.
                                     Kondisi '== 'true'' akan mencentang saklar jika nilainya adalah string 'true'. --}}
                                <input class="form-check-input" type="checkbox" role="switch" id="automatic_stock_management" name="automatic_stock_management" value="true" 
                                    {{ ($settings['automatic_stock_management'] ?? 'false') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="automatic_stock_management">
                                    <strong>Aktifkan Manajemen Stok Otomatis</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Jika diaktifkan, stok produk akan otomatis bertambah saat ada transaksi pembelian dan berkurang saat ada transaksi penjualan. Nonaktifkan jika Anda ingin mengelola stok secara manual.
                            </small>
                        </div>

                        {{-- Di sini nanti bisa ditambahkan pengaturan-pengaturan lainnya --}}

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
