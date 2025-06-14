@extends('karung::layouts.karung_app')

@section('title', 'Edit Transaksi Pembelian - ' . $purchase->purchase_code)

@section('module-content')
<div class="container-fluid">
    {{-- Inisialisasi Alpine.js dengan data detail transaksi yang ada --}}
    <div x-data="purchaseForm({
        initialItems: {{ json_encode($purchase->details->map(function($detail) {
            return [
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'price' => $detail->purchase_price_at_transaction
            ];
        })) }}
    })">
        <form action="{{ route('karung.purchases.update', $purchase->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Method spoofing untuk request UPDATE --}}

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Edit Transaksi Pembelian: {{ $purchase->purchase_code }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- Baris 1: Tanggal & Supplier --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', $purchase->transaction_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                        <option value="">-- Pembelian Umum / Tanpa Supplier --</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Baris 2: No Referensi & Catatan --}}
                            <div class="row mb-4">
                                 <div class="col-md-6">
                                    <label for="purchase_reference_no" class="form-label">No. Referensi/Faktur Supplier (Opsional)</label>
                                    <input type="text" class="form-control @error('purchase_reference_no') is-invalid @enderror" id="purchase_reference_no" name="purchase_reference_no" value="{{ old('purchase_reference_no', $purchase->purchase_reference_no) }}">
                                    @error('purchase_reference_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes', $purchase->notes) }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                                    <input :id="'product-select-' + index" x-init="initTomSelect($el, index, item.product_id)" />
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

                            {{-- Upload Struk --}}
                            <div class="mb-3 mt-3">
                                <label for="attachment_path" class="form-label">Ganti Struk/Nota Pembelian (Opsional)</label>
                                @if($purchase->attachment_path)
                                    <p class="small text-muted">File saat ini: <a href="{{ asset('storage/' . $purchase->attachment_path) }}" target="_blank">Lihat file</a></p>
                                @endif
                                <input class="form-control @error('attachment_path') is-invalid @enderror" type="file" id="attachment_path" name="attachment_path">
                                @error('attachment_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
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
            items: config.initialItems.length > 0 ? config.initialItems : [{ product_id: '', quantity: 1, price: 0 }],
            tomSelectInstances: [],

            initTomSelect(element, index, initialValue) {
                const tomSelect = new TomSelect(element, {
                    options: productsData,
                    placeholder: '-- Pilih atau Cari Produk --',
                    maxItems: 1,
                    items: [initialValue], // Set nilai awal
                    onChange: (value) => {
                        this.productChanged(index, value);
                    }
                });
                this.tomSelectInstances[index] = tomSelect;
            },
            productChanged(index, selectedProductId) {
                this.items[index].product_id = selectedProductId;
                const selectedProduct = productsData.find(p => p.value == selectedProductId);
                if (selectedProduct && this.items[index].price == 0) {
                    this.items[index].price = selectedProduct.purchase_price;
                } else if (!selectedProduct) {
                     this.items[index].price = 0;
                }
            },
            addItem() { this.items.push({ product_id: '', quantity: 1, price: 0 }); },
            removeItem(index) {
                if (this.tomSelectInstances[index]) { this.tomSelectInstances[index].destroy(); }
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
                if (isNaN(value)) return 'Rp 0';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            },
        }
    }
</script>
@endpush