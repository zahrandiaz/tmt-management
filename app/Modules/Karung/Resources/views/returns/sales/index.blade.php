@extends('karung::layouts.karung_app')

@section('title', 'Riwayat Retur Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Retur Penjualan</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">&larr; Kembali ke Dashboard</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kode Retur</th>
                                    <th>Invoice Asli</th>
                                    <th>Pelanggan</th>
                                    <th>Tgl. Retur</th>
                                    <th class="text-end">Total Nilai Retur</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($returns as $return)
                                    <tr>
                                        <td>{{ $return->return_code }}</td>
                                        <td><a href="{{ route('karung.sales.show', $return->originalTransaction->id) }}">{{ $return->originalTransaction->invoice_number }}</a></td>
                                        <td>{{ $return->customer->name }}</td>
                                        <td>{{ $return->return_date->format('d M Y') }}</td>
                                        <td class="text-end">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('karung.returns.sales.show', $return->id) }}" class="btn btn-info btn-sm text-white">Lihat Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center">Belum ada data retur.</td></tr>
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
@endsection