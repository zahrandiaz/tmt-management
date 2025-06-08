@extends('layouts.tmt_app')

@section('title', 'Catat Transaksi Pembelian Baru - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    {{-- Kita bungkus semua dalam satu komponen Alpine.js --}}
    <div x-data="purchaseForm()">
        <form action="{{ route('karung.purchases.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Catat Transaksi Pembelian Baru</h5>
                        </div>
                        <div class="card-body">
                            {{-- Baris 1: Tanggal & Supplier --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                        <option value="">-- Pembelian Umum / Tanpa Supplier --</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Baris 2: No Referensi & Catatan --}}
                            <div class="row mb-4">
                                 <div class="col-md-6">
                                    <label for="purchase_reference_no" class="form-label">No. Referensi/Faktur Supplier (Opsional)</label>
                                    <input type="text" class="form-control @error('purchase_reference_no') is-invalid @enderror" id="purchase_reference_no" name="purchase_reference_no" value="{{ old('purchase_reference_no') }}">
                                    @error('purchase_reference_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Bagian Detail Produk Pembelian --}}
                            <h5 class="mb-3">Detail Produk</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 40%;">Produk <span class="text-danger">*</span></th>
                                            <th scope="col">Jumlah <span class="text-danger">*</span></th>
                                            <th scope="col">Harga Beli / Satuan <span class="text-danger">*</span></th>
                                            <th scope="col">Subtotal</th>
                                            <th scope="col" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Loop dinamis menggunakan Alpine.js --}}
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td>
                                                    {{-- Nama input menggunakan array agar bisa diterima sebagai array di controller --}}
                                                    <select :name="'details[' + index + '][product_id]'" class="form-select" required>
                                                        <option value="">-- Pilih Produk --</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][quantity]'" x-model.number="item.quantity" class="form-control" placeholder="Jumlah" required min="1">
                                                </td>
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][purchase_price_at_transaction]'" x-model.number="item.price" class="form-control" placeholder="Harga Beli" required min="0">
                                                </td>
                                                <td>
                                                    {{-- Subtotal dihitung otomatis --}}
                                                    <input type="text" :value="formatCurrency(item.quantity * item.price)" class="form-control" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" @click="removeItem(index)" class="btn btn-danger btn-sm">
                                                        Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" @click="addItem()" class="btn btn-success btn-sm">
                                                    + Tambah Baris Produk
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total Pembelian:</th>
                                            <td colspan="2">
                                                <input type="text" x-model="formatCurrency(total)" class="form-control fw-bold" readonly>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Upload Struk --}}
                            <div class="mb-3 mt-3">
                                <label for="attachment_path" class="form-label">Upload Struk/Nota Pembelian (Opsional)</label>
                                <input class="form-control @error('attachment_path') is-invalid @enderror" type="file" id="attachment_path" name="attachment_path">
                                @error('attachment_path')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('karung.purchases.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Transaksi Pembelian</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('footer-scripts')
{{-- Memuat Alpine.js dari CDN --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function purchaseForm() {
        return {
            items: [{ product_id: '', quantity: 1, price: 0 }], // Mulai dengan 1 baris kosong

            // Fungsi untuk menambah baris baru
            addItem() {
                this.items.push({
                    product_id: '',
                    quantity: 1,
                    price: 0
                });
            },

            // Fungsi untuk menghapus baris
            removeItem(index) {
                this.items.splice(index, 1);
            },

            // Menghitung total keseluruhan
            get total() {
                return this.items.reduce((sum, item) => {
                    return sum + (item.quantity * item.price);
                }, 0);
            },

            // Fungsi untuk memformat angka menjadi format mata uang
            formatCurrency(value) {
                if (isNaN(value)) {
                    return 'Rp 0';
                }
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            }
        }
    }
</script>
@endpush