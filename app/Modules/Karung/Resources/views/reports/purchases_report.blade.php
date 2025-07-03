{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Pembelian
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid" x-data="{ openRow: null }">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Laporan Pembelian</h5>
                            <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            {{-- Filter Presets --}}
                            <div class="mb-3">
                                <a href="{{ route('karung.reports.purchases', ['preset' => 'today'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                                <a href="{{ route('karung.reports.purchases', ['preset' => 'this_week'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                                <a href="{{ route('karung.reports.purchases', ['preset' => 'this_month'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                                <a href="{{ route('karung.reports.purchases', ['preset' => 'this_year'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                            </div>
                            
                            {{-- Form Filter Manual --}}
                            <form method="GET" action="{{ route('karung.reports.purchases') }}" class="mb-4 p-3 border rounded">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="supplier_id" class="form-label">Supplier</label>
                                        <select name="supplier_id" id="supplier_id" class="form-select">
                                            <option value="">Semua Supplier</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $selectedSupplierId == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter Manual</button>
                                        <a href="{{ route('karung.reports.purchases') }}" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i> Reset Filter</a>
                                    </div>
                                </div>
                            </form>

                            {{-- Tombol Export --}}
                            <div class="mb-4">
                                <strong>Export Laporan:</strong>
                                @php
                                    $exportParams = array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate]);
                                @endphp
                                <a href="{{ route('karung.reports.purchases.export', $exportParams) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                                </a>
                                <a href="{{ route('karung.reports.purchases.export.pdf', $exportParams) }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                </a>
                            </div>
                            
                            <hr>

                            {{-- Ringkasan Laporan --}}
                            <h5 class="mb-3">Ringkasan Laporan</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card text-white bg-primary"><div class="card-body"><h6 class="card-title">Jumlah Transaksi</h6><p class="card-text fs-4 fw-bold">{{ $totalTransactions }} Transaksi</p></div></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card text-white bg-danger"><div class="card-body"><h6 class="card-title">Total Pengeluaran Pembelian</h6><p class="card-text fs-4 fw-bold">Rp {{ number_format($totalSpending, 0, ',', '.') }}</p></div></div>
                                </div>
                            </div>

                            {{-- Tabel Detail --}}
                            <h5 class="mb-3">Tabel Detail Transaksi</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 1%;"></th>
                                            <th style="width: 1%;">#</th>
                                            <th>Kode Pembelian</th>
                                            <th>Tanggal</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Total Pembelian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($purchases as $purchase)
                                            <tr class="align-middle">
                                                <td>
                                                    <button @click="openRow = (openRow === {{ $purchase->id }}) ? null : {{ $purchase->id }}" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi" :class="openRow === {{ $purchase->id }} ? 'bi-dash-lg' : 'bi-plus-lg'"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $loop->iteration + $purchases->firstItem() - 1 }}</td>
                                                <td><a href="{{ route('karung.purchases.show', $purchase->id) }}">{{ $purchase->purchase_code }}</a></td>
                                                <td>{{ $purchase->transaction_date->format('d-m-Y H:i') }}</td>
                                                <td>{{ $purchase->supplier?->name ?: 'Pembelian Umum' }}</td>
                                                <td class="text-end fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr x-show="openRow === {{ $purchase->id }}" style="display: none;" x-collapse>
                                                <td colspan="6" class="p-0">
                                                    <div class="p-3 bg-light">
                                                        <h6 class="ms-2">Rincian Produk:</h6>
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th>Nama Produk</th>
                                                                    <th class="text-center">Kuantitas</th>
                                                                    <th class="text-end">Harga Beli/Pcs</th>
                                                                    <th class="text-end">Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($purchase->details as $detail)
                                                                <tr>
                                                                    <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                                                                    <td class="text-center">{{ $detail->quantity }}</td>
                                                                    <td class="text-end">Rp {{ number_format($detail->purchase_price_at_transaction, 0, ',', '.') }}</td>
                                                                    <td class="text-end">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center">Tidak ada data pembelian untuk periode ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $purchases->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>