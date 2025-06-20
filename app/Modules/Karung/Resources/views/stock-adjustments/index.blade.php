@extends('karung::layouts.karung_app')

@section('title', 'Riwayat Penyesuaian Stok')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Penyesuaian Stok</h5>
                    <a href="{{ route('karung.stock-adjustments.create') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
                        Buat Penyesuaian Baru
                    </a>
                </div>

                <div class="card-body">
                    @include('karung::components.flash-message')

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 15%;">Tanggal</th>
                                    <th>Produk</th>
                                    <th>Tipe</th>
                                    <th class="text-center">Jumlah Penyesuaian</th>
                                    <th class="text-center">Stok Awal</th>
                                    <th class="text-center">Stok Akhir</th>
                                    <th>Alasan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adjustments as $adjustment)
                                    <tr>
                                        <td>{{ $adjustment->created_at->format('d-m-Y H:i') }}</td>
                                        <td>{{ $adjustment->product->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-secondary">{{ $adjustment->type }}</span></td>
                                        <td class="text-center fw-bold {{ $adjustment->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $adjustment->quantity > 0 ? '+' : '' }}{{ $adjustment->quantity }}
                                        </td>
                                        <td class="text-center">{{ $adjustment->stock_before }}</td>
                                        <td class="text-center fw-bold">{{ $adjustment->stock_after }}</td>
                                        <td>{{ $adjustment->reason }}</td>
                                        <td>{{ $adjustment->user->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada riwayat penyesuaian stok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $adjustments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection