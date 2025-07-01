@extends('karung::layouts.karung_app')

@section('title', 'Detail Retur Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Retur: #{{ $salesReturn->return_code }}</h5>
                    <div>
                        {{-- [BARU v1.30] Tombol Download Nota Kredit --}}
                        <a href="{{ route('karung.returns.sales.credit_note.pdf', $salesReturn->id) }}" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf-fill me-1" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572a.27.27 0 0 1-.035.012.28.28 0 0 1-.031-.023c-.075-.041-.158-.1-.218-.17a.85.85 0 0 1-.135-.37c-.014-.042-.027-.102-.038-.172a.21.21 0 0 1 .035-.145c.022-.02.05-.038.083-.051a.2.2 0 0 1 .051-.028.2.2 0 0 1 .068.004q.032.007.07.02z"/><path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM.5 11.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/></svg>
                            Download Nota Kredit
                        </a>
                        <a href="{{ route('karung.returns.sales.index') }}" class="btn btn-light btn-sm">&larr; Kembali ke Riwayat Retur</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6"><p><strong>Kode Retur:</strong> {{ $salesReturn->return_code }}</p><p><strong>Tanggal Retur:</strong> {{ $salesReturn->return_date->format('d F Y') }}</p><p><strong>Invoice Asli:</strong> <a href="{{ route('karung.sales.show', $salesReturn->originalTransaction->id) }}">{{ $salesReturn->originalTransaction->invoice_number }}</a></p></div>
                        <div class="col-md-6"><p><strong>Pelanggan:</strong> {{ $salesReturn->customer->name }}</p><p><strong>Dicatat Oleh:</strong> {{ $salesReturn->user->name }}</p><p><strong>Alasan:</strong> {{ $salesReturn->reason ?: '-' }}</p></div>
                    </div>
                    <h5 class="mb-3">Rincian Produk yang Diretur</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light"><tr><th>No.</th><th>Nama Produk</th><th class="text-center">Jumlah</th><th class="text-end">Harga Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody>
                                @foreach($salesReturn->details as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->product->name }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr class="table-dark"><th colspan="4" class="text-end">TOTAL NILAI RETUR</th><th class="text-end">Rp {{ number_format($salesReturn->total_amount, 0, ',', '.') }}</th></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection