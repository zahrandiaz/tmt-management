{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Performa Pelanggan
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
                    <h5 class="mb-0">Laporan Performa Pelanggan</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <p>Halaman ini menampilkan peringkat pelanggan berdasarkan total belanja dan frekuensi transaksi.</p>
                    <div class="alert alert-secondary border-start-0 border-end-0 border-2 rounded-0 small">
                        <h6 class="alert-heading fw-bold">Penjelasan Metrik:</h6>
                        <ul class="mb-0">
                            <li><strong>Jml. Transaksi:</strong> Total berapa kali pelanggan ini melakukan transaksi.</li>
                            <li><strong>Total Belanja:</strong> Total nilai nominal dari semua transaksi yang dilakukan pelanggan ini.</li>
                            <li><strong>Transaksi Terakhir:</strong> Tanggal terakhir pelanggan melakukan pembelian.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Pelanggan</th>
                                    <th class="text-center">
                                        <a class="text-white" href="{{ route('karung.reports.customer_performance', ['sort_by' => 'transaction_count', 'sort_order' => $sortBy == 'transaction_count' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">
                                            Jml. Transaksi <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                    <th class="text-end">
                                        <a class="text-white" href="{{ route('karung.reports.customer_performance', ['sort_by' => 'total_spent', 'sort_order' => $sortBy == 'total_spent' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">
                                            Total Belanja <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                    <th class="text-center">
                                        <a class="text-white" href="{{ route('karung.reports.customer_performance', ['sort_by' => 'last_purchase_date', 'sort_order' => $sortBy == 'last_purchase_date' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">
                                            Transaksi Terakhir <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                <tr>
                                    <td>{{ $loop->iteration + $customers->firstItem() - 1 }}</td>
                                    <td><a href="{{ route('karung.customers.history', $customer->id) }}">{{ $customer->name }}</a></td>
                                    <td class="text-center">{{ $customer->transaction_count }}</td>
                                    <td class="text-end">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $customer->last_purchase_date ? \Carbon\Carbon::parse($customer->last_purchase_date)->format('d M Y') : '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">Tidak ada data pelanggan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $customers->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>