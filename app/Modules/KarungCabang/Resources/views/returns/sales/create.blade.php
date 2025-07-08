{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Buat Retur Penjualan
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karungcabang::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid" x-data="returnFormHandler()">
            <form action="{{ route('karungcabang.sales.returns.store', $salesTransaction->id) }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Buat Retur untuk Invoice #{{ $salesTransaction->invoice_number }}</h5>
                        <a href="{{ route('karungcabang.sales.show', $salesTransaction->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-x-circle"></i> Batal</a>
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
                                            <input class="form-check-input" type="checkbox" value="{{ $detail->id }}" x-model="checkedItems">
                                            {{-- Input hidden ini akan diaktifkan/dinonaktifkan berdasarkan checkbox --}}
                                            <input type="hidden" name="items[{{ $detail->id }}][sales_transaction_detail_id]" value="{{ $detail->id }}" :disabled="!isChecked({{ $detail->id }})">
                                            <input type="hidden" name="items[{{ $detail->id }}][product_id]" value="{{ $detail->product_id }}" :disabled="!isChecked({{ $detail->id }})">
                                        </td>
                                        <td>{{ $detail->product->name }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="items[{{ $detail->id }}][return_quantity]" value="1" min="1" max="{{ $detail->quantity }}" :disabled="!isChecked({{ $detail->id }})" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary" :disabled="checkedItems.length === 0">
                            <i class="bi bi-save-fill me-1"></i> Proses Retur
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            // Menggunakan logika asli yang sederhana dari kode lama Anda yang sudah terbukti bekerja
            function returnFormHandler() {
                return {
                    checkedItems: [],
                    isChecked(id) {
                        return this.checkedItems.includes(id.toString());
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>