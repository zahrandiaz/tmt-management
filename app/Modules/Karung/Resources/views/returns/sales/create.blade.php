@extends('karung::layouts.karung_app')

@section('title', 'Buat Retur Penjualan - Modul Toko Karung')

@section('module-content')
<div class="container-fluid" x-data="returnFormHandler()">
    <form action="{{ route('karung.sales.returns.store', $salesTransaction->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Buat Retur untuk Invoice #{{ $salesTransaction->invoice_number }}</h5>
                        <a href="{{ route('karung.sales.show', $salesTransaction->id) }}" class="btn btn-secondary btn-sm">&larr; Batal</a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <strong>Whoops! Ada beberapa masalah dengan input Anda.</strong>
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="return_date" class="form-label">Tanggal Retur</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reason" class="form-label">Alasan Retur (Opsional)</label>
                                <input type="text" class="form-control" id="reason" name="reason" value="{{ old('reason') }}" placeholder="Contoh: Barang rusak, salah ukuran">
                            </div>
                        </div>

                        <h6 class="mt-4">Pilih Produk yang Akan Diretur</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">Pilih</th>
                                        <th>Nama Produk</th>
                                        <th class="text-center">Jml. Beli</th>
                                        <th class="text-center" style="width: 15%;">Jml. Retur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesTransaction->details as $detail)
                                    <tr>
                                        <td>
                                            {{-- Checkbox utama tetap sama --}}
                                            <input class="form-check-input" type="checkbox" value="{{ $detail->id }}" 
                                                x-model="checkedItems" :id="'item-check-{{ $detail->id }}'">
                                            
                                            {{-- [MODIFIKASI] Tambahkan :disabled pada input-input hidden --}}
                                            <input type="hidden" name="items[{{ $detail->id }}][sales_transaction_detail_id]" value="{{ $detail->id }}" :disabled="!isChecked({{ $detail->id }})">
                                            <input type="hidden" name="items[{{ $detail->id }}][product_id]" value="{{ $detail->product_id }}" :disabled="!isChecked({{ $detail->id }})">
                                        </td>
                                        <td>{{ $detail->product->name }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td>
                                            {{-- Input jumlah retur tidak berubah, karena sudah benar --}}
                                            <input type="number" class="form-control form-control-sm" name="items[{{ $detail->id }}][return_quantity]" value="1" min="1" max="{{ $detail->quantity }}" 
                                                :disabled="!isChecked({{ $detail->id }})" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary" :disabled="checkedItems.length === 0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save-fill me-1" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v6h-2a.5.5 0 0 0-.354.854l2.5 2.5a.5.5 0 0 0 .708 0l2.5-2.5A.5.5 0 0 0 10.5 7.5h-2z"/></svg>
                            Proses Retur
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function returnFormHandler() {
        return {
            checkedItems: [],
            isChecked(id) {
                return this.checkedItems.includes(id.toString());
            }
        }
    }
</script>
@endsection