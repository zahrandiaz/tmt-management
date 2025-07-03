{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Retur Pembelian
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
                            <h5 class="mb-0">Riwayat Retur Pembelian</h5>
                            <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left-circle"></i> Kembali ke Dashboard</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kode Retur</th>
                                            <th>Kode Pembelian Asli</th>
                                            <th>Supplier</th>
                                            <th>Tgl. Retur</th>
                                            <th class="text-end">Total Nilai Retur</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($returns as $return)
                                            <tr>
                                                <td>{{ $return->return_code }}</td>
                                                <td><a href="{{ route('karung.purchases.show', $return->originalTransaction->id) }}">{{ $return->originalTransaction->purchase_code }}</a></td>
                                                <td>{{ $return->supplier->name ?? 'N/A' }}</td>
                                                <td>{{ $return->return_date->format('d M Y') }}</td>
                                                <td class="text-end">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('karung.returns.purchases.show', $return->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center">Belum ada data retur pembelian.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $returns->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>