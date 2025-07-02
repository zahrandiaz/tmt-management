{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>

    {{-- Mengisi slot 'header' di layout utama dengan judul halaman --}}
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Daftar Pelanggan
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
                            <h5 class="mb-0">Daftar Pelanggan</h5>
                            <div>
                                <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                                </a>
                                @can('karung.manage_customers')
                                <a href="{{ route('karung.customers.create') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Pelanggan Baru
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
                                <form action="{{ route('karung.customers.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan Nama, Kode, atau No. Telepon..." value="{{ request('search') }}">
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
                                            <th scope="col">Kode</th>
                                            <th scope="col">Nama Pelanggan</th>
                                            <th scope="col">No. Telepon</th>
                                            <th scope="col">Email</th>
                                            <th scope="col" style="width: 15%;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customers as $index => $customer)
                                            <tr>
                                                <th scope="row">{{ $customers->firstItem() + $index }}</th>
                                                <td>{{ $customer->customer_code ?: '-' }}</td>
                                                <td>{{ $customer->name }}</td>
                                                <td>{{ $customer->phone_number ?: '-' }}</td>
                                                <td>{{ $customer->email ?: '-' }}</td>
                                                <td class="text-center">
                                                    @can('karung.manage_customers')
                                                    <a href="{{ route('karung.customers.history', $customer->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Riwayat Transaksi">
                                                        <i class="bi bi-clock-history"></i>
                                                    </a>
                                                    <a href="{{ route('karung.customers.edit', $customer->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form action="{{ route('karung.customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?');">
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
                                                <td colspan="6" class="text-center">Tidak ada data pelanggan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Link Paginasi --}}
                            <div class="mt-3">
                                {{ $customers->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </x-module-layout>
</x-app-layout>