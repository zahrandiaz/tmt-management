@extends('karung::layouts.karung_app')

@section('title', 'Laporan Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid" x-data="{ openRow: null }">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Penjualan</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/></svg>
                        Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    {{-- Form Filter tidak berubah --}}
                    <div class="mb-3">
                        <a href="{{ route('karung.reports.sales', ['preset' => 'today'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                        <a href="{{ route('karung.reports.sales', ['preset' => 'this_week'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                        <a href="{{ route('karung.reports.sales', ['preset' => 'this_month'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                        <a href="{{ route('karung.reports.sales', ['preset' => 'this_year'] + request()->except(['preset', 'start_date', 'end_date'])) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                    </div>

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
                                <button type="submit" class="btn btn-primary">Filter Manual</button>
                                <a href="{{ route('karung.reports.sales') }}" class="btn btn-secondary">Reset Filter</a>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4">
                        <strong>Export Laporan:</strong>
                        {{-- [MODIFIKASI] Menambahkan parameter tanggal ke route export --}}
                        @php
                            $exportParams = array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate]);
                        @endphp
                        <a href="{{ route('karung.reports.sales.export', $exportParams) }}" class="btn btn-success btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                            Excel
                        </a>
                        <a href="{{ route('karung.reports.sales.export.pdf', $exportParams) }}" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf-fill" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572a.27.27 0 0 1-.035.012.28.28 0 0 1-.031-.023c-.075-.041-.158-.1-.218-.17a.85.85 0 0 1-.135-.37c-.014-.042-.027-.102-.038-.172a.21.21 0 0 1 .035-.145c.022-.02.05-.038.083-.051a.2.2 0 0 1 .051-.028.2.2 0 0 1 .068.004q.032.007.07.02z"/><path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM.5 11.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/></svg>
                            PDF
                        </a>
                    </div>
                    
                    <hr>

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
                                        // [MODIFIKASI] Kalkulasi Laba per baris sekarang menggunakan data akurat dari 'total_cost'
                                        $totalLaba = $sale->total_amount - $sale->total_cost;
                                    @endphp
                                    <tr class="align-middle">
                                        <td>
                                            <button @click="openRow = (openRow === {{ $sale->id }}) ? null : {{ $sale->id }}" class="btn btn-sm btn-outline-secondary">
                                                <svg x-show="openRow !== {{ $sale->id }}" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
                                                <svg x-show="openRow === {{ $sale->id }}" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/></svg>
                                            </button>
                                        </td>
                                        <td>{{ $loop->iteration + $sales->firstItem() - 1 }}</td>
                                        <td><a href="{{ route('karung.sales.show', $sale->id) }}">{{ $sale->invoice_number }}</a></td>
                                        <td>{{ $sale->transaction_date->format('d-m-Y H:i') }}</td>
                                        <td>{{ $sale->customer->name ?? 'Penjualan Umum' }}</td>
                                        {{-- [MODIFIKASI] Gunakan 'total_cost' yang sudah dihitung akurat --}}
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
                                                            // [MODIFIKASI] Kalkulasi subtotal laba menggunakan HPP historis
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
@endsection