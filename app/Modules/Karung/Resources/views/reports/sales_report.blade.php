{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Penjualan
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
                            <h5 class="mb-0">Laporan Penjualan</h5>
                            <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            {{-- Filter Presets --}}
                            <div class="mb-3">
                                <a href="{{ route('karung.reports.sales', ['preset' => 'today'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                                <a href="{{ route('karung.reports.sales', ['preset' => 'this_week'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                                <a href="{{ route('karung.reports.sales', ['preset' => 'this_month'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                                <a href="{{ route('karung.reports.sales', ['preset' => 'this_year'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                            </div>

                            {{-- Form Filter Manual --}}
                            <form method="GET" action="{{ route('karung.reports.sales') }}" class="mb-4 p-3 border rounded">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="customer_id" class="form-label">Pelanggan</label>
                                        <select name="customer_id" id="customer_id" class="form-select">
                                            <option value="">Semua Pelanggan</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="user_id" class="form-label">Kasir</label>
                                        <select name="user_id" id="user_id" class="form-select">
                                            <option value="">Semua Kasir</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter Manual</button>
                                        <a href="{{ route('karung.reports.sales') }}" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i> Reset Filter</a>
                                    </div>
                                </div>
                            </form>

                            {{-- Tombol Export --}}
                            <div class="mb-4">
                                <strong>Export Laporan:</strong>
                                @php
                                    $exportParams = array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate]);
                                @endphp
                                <a href="{{ route('karung.reports.sales.export', $exportParams) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                                </a>
                                <a href="{{ route('karung.reports.sales.export.pdf', $exportParams) }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                </a>
                            </div>
                            
                            <hr>

                            {{-- Ringkasan Laporan --}}
                            <h5 class="mb-3">Ringkasan Laporan</h5>
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <div class="card text-white bg-primary"><div class="card-body"><h6 class="card-title">Jumlah Transaksi</h6><p class="card-text fs-4 fw-bold">{{ $totalTransactions }}</p></div></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card text-white bg-danger"><div class="card-body"><h6 class="card-title">Total Modal (HPP)</h6><p class="card-text fs-4 fw-bold">Rp {{ number_format($totalCost, 0, ',', '.') }}</p></div></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card text-white bg-warning text-dark"><div class="card-body"><h6 class="card-title">Total Laba Kotor</h6><p class="card-text fs-4 fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p></div></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card text-white bg-success"><div class="card-body"><h6 class="card-title">Total Pendapatan</h6><p class="card-text fs-4 fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p></div></div>
                                </div>
                            </div>

                            {{-- Tabel Detail --}}
                            <h5 class="mb-3">Tabel Detail Transaksi</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="width: 1%;"></th>
                                            <th scope="col" style="width: 1%;">#</th>
                                            <th scope="col">Invoice</th>
                                            <th scope="col">Tanggal</th>
                                            <th scope="col">Pelanggan</th>
                                            <th scope="col" class="text-end">Total Modal</th>
                                            <th scope="col" class="text-end">Total Laba</th>
                                            <th scope="col" class="text-end">Total Penjualan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($sales as $sale)
                                            @php
                                                $totalLaba = $sale->total_amount - $sale->total_cost;
                                            @endphp
                                            <tr class="align-middle">
                                                <td>
                                                    <button @click="openRow = (openRow === {{ $sale->id }}) ? null : {{ $sale->id }}" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi" :class="openRow === {{ $sale->id }} ? 'bi-dash-lg' : 'bi-plus-lg'"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $loop->iteration + $sales->firstItem() - 1 }}</td>
                                                <td><a href="{{ route('karung.sales.show', $sale->id) }}">{{ $sale->invoice_number }}</a></td>
                                                <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                                                <td>{{ $sale->customer->name ?? 'Penjualan Umum' }}</td>
                                                <td class="text-end">Rp {{ number_format($sale->total_cost, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold {{ $totalLaba >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalLaba, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr x-show="openRow === {{ $sale->id }}" style="display: none;" x-collapse>
                                                <td colspan="8" class="p-0">
                                                    <div class="p-3 bg-light">
                                                        <h6 class="ms-2">Rincian Produk:</h6>
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th>Nama Produk</th>
                                                                    <th class="text-center">Kuantitas</th>
                                                                    <th class="text-end">Harga Modal/Pcs</th>
                                                                    <th class="text-end">Harga Jual/Pcs</th>
                                                                    <th class="text-end">Subtotal Laba</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($sale->details as $detail)
                                                                @php
                                                                    $modalPerPcs = $detail->purchase_price_at_sale ?? 0;
                                                                    $subLaba = ($detail->selling_price_at_transaction - $modalPerPcs) * $detail->quantity;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                                                                    <td class="text-center">{{ $detail->quantity }}</td>
                                                                    <td class="text-end">Rp {{ number_format($modalPerPcs, 0, ',', '.') }}</td>
                                                                    <td class="text-end">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                                                    <td class="text-end fw-bold {{ $subLaba >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($subLaba, 0, ',', '.') }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center">Tidak ada data penjualan untuk periode ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $sales->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            document.addEventListener('alpine:init', () => {
                // Plugin x-collapse untuk Alpine.js
                Alpine.plugin(Alpine.plugins.collapse);
            });
            // Pastikan Alpine dan plugin collapse sudah dimuat
            // Biasanya script ini ada di app.js atau layout utama
            if (typeof Alpine !== 'undefined' && typeof Alpine.plugins === 'undefined') {
                Alpine.plugins = {};
            }
            if (typeof Alpine !== 'undefined' && typeof Alpine.plugins.collapse === 'undefined') {
                Alpine.plugins.collapse = function (Alpine) {
                    Alpine.directive('collapse', (el, { expression }, { effect, evaluateLater }) => {
                        let master = evaluateLater(expression || 'true')
                        let isShowing = false
                        let whenitshow = () => { if (!isShowing) effect(() => { isShowing = true; el.style.display = 'table-row'; }); };
                        let whenithide = () => { if (isShowing) effect(() => { isShowing = false; el.style.display = 'none'; }); };
                        master(value => value ? whenitshow() : whenithide());
                    });
                }
            }
        </script>
    </x-slot>
</x-app-layout>