{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Performa Produk
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
                    <h5 class="mb-0">Laporan Performa Produk</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <p>Halaman ini menampilkan peringkat produk berdasarkan penjualan dan profitabilitas.</p>

                    <div class="alert alert-secondary border-start-0 border-end-0 border-2 rounded-0 small">
                        <h6 class="alert-heading fw-bold">Penjelasan Metrik:</h6>
                        <ul class="mb-0">
                            <li><strong>Unit Terjual:</strong> Total kuantitas produk ini yang berhasil terjual.</li>
                            <li><strong>Total Omzet:</strong> Total pendapatan kotor dari penjualan produk ini (Jumlah Terjual &times; Harga Jual).</li>
                            <li><strong>Total Laba:</strong> Total keuntungan bersih dari produk ini (Total Omzet - Total Modal dari Harga Beli Referensi).</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Produk</th>
                                    <th class="text-center">
                                        <a class="text-white" href="{{ route('karung.reports.product_performance', ['sort_by' => 'units_sold', 'sort_order' => $sortBy == 'units_sold' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">
                                            Unit Terjual <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                    <th class="text-end">
                                        <a class="text-white" href="{{ route('karung.reports.product_performance', ['sort_by' => 'total_revenue', 'sort_order' => $sortBy == 'total_revenue' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">
                                            Total Omzet <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                    <th class="text-end">
                                        <a class="text-white" href="{{ route('karung.reports.product_performance', ['sort_by' => 'total_profit', 'sort_order' => $sortBy == 'total_profit' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">
                                            Total Laba <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration + $products->firstItem() - 1 }}</td>
                                    <td><a href="{{ route('karung.reports.stock.history', $product->id) }}">{{ $product->name }}</a></td>
                                    <td class="text-center">{{ $product->units_sold ?? 0 }}</td>
                                    <td class="text-end">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($product->total_profit, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">Tidak ada data produk.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $products->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>