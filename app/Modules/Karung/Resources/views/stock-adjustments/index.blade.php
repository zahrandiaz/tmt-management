{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Penyesuaian Stok
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
                            <h5 class="mb-0">Riwayat Penyesuaian Stok</h5>
                            <a href="{{ route('karung.stock-adjustments.create') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-plus-circle-fill"></i> Buat Penyesuaian Baru
                            </a>
                        </div>

                        <div class="card-body">
                            <x-flash-message />

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 15%;">Tanggal</th>
                                            <th>Produk</th>
                                            <th>Tipe</th>
                                            <th class="text-center">Jumlah Penyesuaian</th>
                                            <th class="text-center">Stok Awal</th>
                                            <th class="text-center">Stok Akhir</th>
                                            <th>Alasan</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($adjustments as $adjustment)
                                            <tr>
                                                <td>{{ $adjustment->created_at->format('d-m-Y H:i') }}</td>
                                                <td>{{ $adjustment->product->name ?? 'N/A' }}</td>
                                                <td><span class="badge bg-secondary">{{ $adjustment->type }}</span></td>
                                                <td class="text-center fw-bold {{ $adjustment->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $adjustment->quantity > 0 ? '+' : '' }}{{ $adjustment->quantity }}
                                                </td>
                                                <td class="text-center">{{ $adjustment->stock_before }}</td>
                                                <td class="text-center fw-bold">{{ $adjustment->stock_after }}</td>
                                                <td>{{ $adjustment->reason }}</td>
                                                <td>{{ $adjustment->user->name ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Belum ada riwayat penyesuaian stok.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $adjustments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>