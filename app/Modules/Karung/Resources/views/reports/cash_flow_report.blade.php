{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Arus Kas
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
                    <h5 class="mb-0">Laporan Arus Kas</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    {{-- Filter Presets --}}
                    <div class="mb-3">
                        <a href="{{ route('karung.reports.cash_flow', ['preset' => 'today']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                        <a href="{{ route('karung.reports.cash_flow', ['preset' => 'this_week']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                        <a href="{{ route('karung.reports.cash_flow', ['preset' => 'this_month']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                        <a href="{{ route('karung.reports.cash_flow', ['preset' => 'this_year']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                    </div>

                    {{-- Form Filter Manual --}}
                    <form method="GET" action="{{ route('karung.reports.cash_flow') }}" class="mb-4 p-3 border rounded">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-5">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                    <hr>

                    {{-- Ringkasan Laporan --}}
                    <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}</h5>
                    
                    <div class="row text-center g-3 mt-3">
                        <div class="col-lg-3">
                            <div class="card text-white bg-success h-100">
                                <div class="card-body p-3">
                                    <h6 class="card-title">(+) Pemasukan (Penjualan)</h6>
                                    <p class="card-text fs-4 fw-bold mb-0">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card text-white bg-danger h-100">
                                <div class="card-body p-3">
                                    <h6 class="card-title">(-) Pengeluaran (Pembelian)</h6>
                                    <p class="card-text fs-4 fw-bold mb-0">Rp {{ number_format($purchaseExpense, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card text-dark bg-warning h-100">
                                <div class="card-body p-3">
                                    <h6 class="card-title">(-) Pengeluaran (Biaya Operasional)</h6>
                                    <p class="card-text fs-4 fw-bold mb-0">Rp {{ number_format($operationalExpense, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-3">
                            <div class="card text-white bg-primary h-100">
                                <div class="card-body p-3">
                                    <h6 class="card-title">(=) Arus Kas Bersih</h6>
                                    <p class="card-text fs-4 fw-bold mb-0 {{ $netCashFlow < 0 ? 'text-danger-emphasis' : '' }}">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Transaksi Belum Lunas --}}
                    <div class="mt-5 pt-4 border-top">
                        <h5 class="mb-3 text-muted">Catatan Transaksi di Luar Arus Kas</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Piutang (Penjualan Belum Lunas)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Invoice</th>
                                                <th>Pelanggan</th>
                                                <th class="text-end">Sisa Tagihan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pendingReceivables as $sale)
                                                <tr>
                                                    <td><a href="{{ route('karung.sales.show', $sale) }}">{{ $sale->invoice_number }}</a></td>
                                                    <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                                    <td class="text-end text-danger fw-bold">Rp {{ number_format($sale->total_amount - $sale->amount_paid, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted">Tidak ada piutang pada periode ini.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Utang (Pembelian Belum Lunas)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kode Pembelian</th>
                                                <th>Supplier</th>
                                                <th class="text-end">Sisa Utang</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pendingPayables as $purchase)
                                                <tr>
                                                    <td><a href="{{ route('karung.purchases.show', $purchase) }}">{{ $purchase->purchase_code }}</a></td>
                                                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                                    <td class="text-end text-danger fw-bold">Rp {{ number_format($purchase->total_amount - $purchase->amount_paid, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted">Tidak ada utang pada periode ini.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
</x-app-layout>