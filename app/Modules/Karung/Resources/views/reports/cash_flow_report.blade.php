@extends('karung::layouts.karung_app')

@section('title', 'Laporan Arus Kas')

@section('module-content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Arus Kas</h5>
            <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">...</svg>
                Kembali ke Dashboard
            </a>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('karung.reports.cash_flow', ['preset' => 'today']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                <a href="{{ route('karung.reports.cash_flow', ['preset' => 'this_week']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                <a href="{{ route('karung.reports.cash_flow', ['preset' => 'this_month']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                <a href="{{ route('karung.reports.cash_flow', ['preset' => 'this_year']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
            </div>

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
                        <button type="submit" class="btn btn-primary w-100">Filter Manual</button>
                    </div>
                </div>
            </form>
            
            <hr>

            <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}</h5>
            
            {{-- [MODIFIKASI] Tampilan kartu ringkasan --}}
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
            
        </div>
    </div>
</div>
@endsection