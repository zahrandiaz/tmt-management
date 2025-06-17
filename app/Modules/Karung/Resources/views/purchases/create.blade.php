@extends('karung::layouts.karung_app')

@section('title', 'Catat Transaksi Pembelian Baru - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div x-data="purchaseForm({ productsData: productsData, payment_status: '{{ old('payment_status', 'Lunas') }}', amount_paid: '{{ old('amount_paid', 0) }}' })">
        <form action="{{ route('karung.purchases.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Catat Transaksi Pembelian Baru</h5>
                        </div>
                        <div class="card-body">
                             <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}" required>
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
                                </div>
                            </div>
                            <div class="row mb-4">
                                 <div class="col-md-6">
                                    <label for="purchase_reference_no" class="form-label">No. Referensi/Faktur Supplier (Opsional)</label>
                                    <input type="text" class="form-control @error('purchase_reference_no') is-invalid @enderror" id="purchase_reference_no" name="purchase_reference_no" value="{{ old('purchase_reference_no') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                                </div>
                            </div>

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
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td>
                                                    <input type="hidden" :name="'details[' + index + '][product_id]'" x-model="item.product_id">
                                                    <input :id="'product-select-' + index" x-init="initTomSelect($el, index)" />
                                                </td>
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][quantity]'" x-model.number="item.quantity" @input="item.quantity = Math.max(1, item.quantity)" class="form-control" placeholder="Jumlah" required min="1">
                                                </td>
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][purchase_price_at_transaction]'" x-model.number="item.price" class="form-control" placeholder="Harga Beli" required min="0">
                                                </td>
                                                <td>
                                                    <input type="text" :value="formatCurrency(item.quantity * item.price)" class="form-control bg-light" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" @click="removeItem(index)" class="btn btn-danger btn-sm">&times;</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" @click="addItem()" class="btn btn-success btn-sm">+ Tambah Baris Produk</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total Pembelian:</th>
                                            <td colspan="2">
                                                <input type="text" :value="formatCurrency(total)" class="form-control fw-bold fs-5 text-end bg-light" readonly>
                                                <input type="hidden" name="total_amount" :value="total">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="mb-3 mt-3">
                                <label for="attachment_path" class="form-label">Upload Struk/Nota Pembelian (Opsional)</label>
                                <input class="form-control @error('attachment_path') is-invalid @enderror" type="file" id="attachment_path" name="attachment_path">
                            </div>

                            {{-- [BARU] BLOK PEMBAYARAN --}}
                            <hr class="my-4">
                            <h5 class="mb-3">Detail Pembayaran</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                        <option value="Tunai" {{ old('payment_method') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="Transfer Bank" {{ old('payment_method') == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                        <option value="Lainnya" {{ old('payment_method') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="payment_status" class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" x-model="payment_status" required>
                                        <option value="Lunas">Lunas</option>
                                        <option value="Belum Lunas">Belum Lunas</option>
                                    </select>
                                    @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <template x-if="payment_status === 'Belum Lunas'">
                                    <div class="col-md-4 mb-3">
                                        <label for="amount_paid" class="form-label">Jumlah Dibayar (DP)</label>
                                        <input type="number" step="any" class="form-control @error('amount_paid') is-invalid @enderror" id="amount_paid" name="amount_paid" x-model.number="amount_paid" min="0">
                                        @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </template>
                            </div>
                            {{-- AKHIR BLOK PEMBAYARAN --}}

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('karung.purchases.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary" :disabled="items.length === 0 || items.some(item => !item.product_id)">Simpan Transaksi Pembelian</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $productsJson = $products->map(function($product) {
        return [
            'value' => $product->id,
            'text' => $product->name,
            'purchase_price' => $product->purchase_price,
        ];
    });
@endphp
<script>
    const productsData = @json($productsJson);
</script>

@push('footer-scripts')
<script>
    function purchaseForm(config) {
        return {
            items: [{ product_id: '', quantity: 1, price: 0 }],
            tomSelectInstances: [],
            payment_status: config.payment_status || 'Lunas',
            amount_paid: config.amount_paid || 0,

            initTomSelect(element, index) {
                const tomSelect = new TomSelect(element, {
                    options: productsData,
                    placeholder: '-- Pilih atau Cari Produk --',
                    maxItems: 1,
                    onChange: (value) => {
                        this.productChanged(index, value);
                    }
                });
                this.tomSelectInstances[index] = tomSelect;
            },

            productChanged(index, selectedProductId) {
                this.items[index].product_id = selectedProductId;
                const selectedProduct = productsData.find(p => p.value == selectedProductId);
                if (selectedProduct) {
                    this.items[index].price = selectedProduct.purchase_price;
                } else {
                    this.items[index].price = 0;
                }
            },

            addItem() {
                this.items.push({ product_id: '', quantity: 1, price: 0 });
            },

            removeItem(index) {
                if (this.tomSelectInstances[index]) {
                    this.tomSelectInstances[index].destroy();
                }
                this.items.splice(index, 1);
                this.tomSelectInstances.splice(index, 1);
            },

            get total() {
                return this.items.reduce((sum, item) => {
                    const quantity = isNaN(item.quantity) || item.quantity < 1 ? 0 : item.quantity;
                    const price = isNaN(item.price) ? 0 : item.price;
                    return sum + (quantity * price);
                }, 0);
            },

            formatCurrency(value) {
                if (isNaN(value)) {
                    return 'Rp 0';
                }
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            },
        }
    }
</script>
@endpush