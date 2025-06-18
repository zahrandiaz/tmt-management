@extends('karung::layouts.karung_app')

@section('title', 'Laporan Performa Pelanggan')

@section('module-content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Performa Pelanggan</h5>
            <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">Kembali ke Dashboard</a>
        </div>
        <div class="card-body">
            <p>Halaman ini menampilkan peringkat pelanggan berdasarkan total belanja dan frekuensi transaksi.</p>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama Pelanggan</th>
                            <th class="text-center"><a class="text-white" href="{{ route('karung.reports.customer_performance', ['sort_by' => 'transaction_count', 'sort_order' => $sortBy == 'transaction_count' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">Jml. Transaksi</a></th>
                            <th class="text-end"><a class="text-white" href="{{ route('karung.reports.customer_performance', ['sort_by' => 'total_spent', 'sort_order' => $sortBy == 'total_spent' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">Total Belanja</a></th>
                            <th class="text-center"><a class="text-white" href="{{ route('karung.reports.customer_performance', ['sort_by' => 'last_purchase_date', 'sort_order' => $sortBy == 'last_purchase_date' && $sortOrder == 'desc' ? 'asc' : 'desc']) }}">Transaksi Terakhir</a></th>
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
@endsection