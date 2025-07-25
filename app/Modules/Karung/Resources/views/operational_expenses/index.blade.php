{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Manajemen Biaya Operasional
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Biaya Operasional</h5>
                    <a href="{{ route('karung.operational-expenses.create') }}" class="btn btn-light btn-sm"><i class="bi bi-plus-circle-fill"></i> Tambah Biaya</a>
                </div>
                <div class="card-body">
                    @include('karung::components.flash-message')
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Terkait Transaksi</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Dicatat Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->date->format('d M Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ $expense->category }}</span></td>
                                    <td>{{ $expense->description }}</td>
                                    <td>
                                        @if($expense->salesTransaction)
                                            <a href="{{ route('karung.sales.show', $expense->sales_transaction_id) }}" class="badge bg-success text-decoration-none">
                                                Penjualan: {{ $expense->salesTransaction->invoice_number }}
                                            </a>
                                        @elseif($expense->purchaseTransaction)
                                            <a href="{{ route('karung.purchases.show', $expense->purchase_transaction_id) }}" class="badge bg-info text-decoration-none">
                                                Pembelian: {{ $expense->purchaseTransaction->purchase_code }}
                                            </a>
                                        @else
                                            <span class="text-muted fst-italic">Umum</span>
                                        @endif
                                    </td>
                                    <td class="text-end">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                    <td>{{ $expense->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('karung.operational-expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <form action="{{ route('karung.operational-expenses.destroy', $expense->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash3-fill"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center">Belum ada data biaya operasional.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $expenses->links() }}</div>
                </div>
            </div>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.delete-form').forEach(form => {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Anda yakin?',
                            text: "Data biaya ini akan dihapus permanen.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>