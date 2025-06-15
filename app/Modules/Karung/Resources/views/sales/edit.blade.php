@extends('karung::layouts.karung_app')

@section('title', 'Edit Transaksi Penjualan - ' . $sale->invoice_number)

@section('module-content')
<div class="container-fluid">
    <div x-data="salesForm({
        initialItems: {{ json_encode($sale->details->map(function($detail) {
            return [
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'price' => $detail->selling_price_at_transaction
            ];
        })) }}
    })">
        <form action="{{ route('karung.sales.update', $sale->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Edit Transaksi Penjualan: {{ $sale->invoice_number }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- Data Utama Transaksi --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', $sale->transaction_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">Pelanggan</label>
                                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                        <option value="">-- Penjualan Umum --</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes', $sale->notes) }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Detail Produk --}}
                            <h5 class="mb-3">Detail Produk</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 40%;">Produk <span class="text-danger">*</span></th>
                                            <th scope="col">Jumlah <span class="text-danger">*</span></th>
                                            <th scope="col">Harga Jual / Satuan <span class="text-danger">*</span></th>
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
                                                    <input type="number" :name="'details[' + index + '][selling_price_at_transaction]'" x-model.number="item.price" class="form-control" placeholder="Harga Jual" required min="0">
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
                                            <th colspan="3" class="text-end">TOTAL PENJUALAN:</th>
                                            <td colspan="2">
                                                <input type="text" :value="formatCurrency(total)" class="form-control fw-bold fs-5 text-end bg-light" readonly>
                                                <input type="hidden" name="total_amount" :value="total">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @error('details') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

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
            'text' => $product->name . ' (Stok: ' . $product->stock . ')',
            'selling_price' => $product->selling_price,
        ];
    });
@endphp
<script>
    const productsData = @json($productsJson);
</script>

@push('footer-scripts')
<script>
    function salesForm(config) {
        return {
            items: config.initialItems.length > 0 ? config.initialItems : [{ product_id: '', quantity: 1, price: 0 }],
            tomSelectInstances: [],
            initTomSelect(element, index, initialValue) {
                const tomSelect = new TomSelect(element, {
                    options: productsData,
                    placeholder: '-- Pilih atau Cari Produk --',
                    maxItems: 1,
                    items: [initialValue],
                    onChange: (value) => { this.productChanged(index, value); }
                });
                this.tomSelectInstances[index] = tomSelect;
            },
            productChanged(index, selectedProductId) {
                this.items[index].product_id = selectedProductId;
                const selectedProduct = productsData.find(p => p.value == selectedProductId);
                if (selectedProduct && this.items[index].price == 0) {
                    this.items[index].price = selectedProduct.selling_price;
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
            }
        }
    }
</script>
@endpush