{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Edit Transaksi Pembelian: {{ $purchase->purchase_code }}
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <div x-data="purchaseForm({
                initialItems: {{ json_encode($purchase->details->map(function($detail) {
                    if (!$detail->product) return null;
                    return [
                        'product_id' => $detail->product_id,
                        'quantity' => $detail->quantity,
                        'price' => $detail->purchase_price_at_transaction
                    ];
                })->filter()) }},
                productsData: productsData,
                payment_status: '{{ old('payment_status', $purchase->payment_status) }}',
                amount_paid: '{{ old('amount_paid', $purchase->amount_paid) }}'
            })">
                <form action="{{ route('karung.purchases.update', $purchase->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Edit Transaksi Pembelian: {{ $purchase->purchase_code }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- Form Fields --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="transaction_date" value="{{ old('transaction_date', $purchase->transaction_date->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select" name="supplier_id" id="supplier_id_select">
                                        <option value="">-- Pembelian Umum / Tanpa Supplier --</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="purchase_reference_no" class="form-label">No. Referensi/Faktur Supplier (Opsional)</label>
                                    <input type="text" class="form-control" name="purchase_reference_no" value="{{ old('purchase_reference_no', $purchase->purchase_reference_no) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control" name="notes" rows="1">{{ old('notes', $purchase->notes) }}</textarea>
                                </div>
                            </div>

                            {{-- Details Table --}}
                            <h5 class="mb-3">Detail Produk</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Produk <span class="text-danger">*</span></th>
                                            <th>Jumlah <span class="text-danger">*</span></th>
                                            <th>Harga Beli / Satuan <span class="text-danger">*</span></th>
                                            <th>Subtotal</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td>
                                                    <input type="hidden" :name="'details[' + index + '][product_id]'" x-model="item.product_id">
                                                    <input :id="'product-select-' + index" x-init="initTomSelect($el, index)" />
                                                </td>
                                                <td><input type="number" :name="'details[' + index + '][quantity]'" x-model.number="item.quantity" @input="item.quantity = Math.max(1, item.quantity)" class="form-control" required min="1"></td>
                                                <td><input type="number" :name="'details[' + index + '][purchase_price_at_transaction]'" x-model.number="item.price" class="form-control" required min="0"></td>
                                                <td><input type="text" :value="formatCurrency(item.quantity * item.price)" class="form-control bg-light" readonly></td>
                                                <td class="text-center"><button type="button" @click="removeItem(index)" class="btn btn-danger btn-sm">&times;</button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5"><button type="button" @click="addItem()" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Tambah Baris</button></td>
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
                            @error('details') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                            {{-- Attachment & Payment --}}
                            <div class="mb-3 mt-3">
                                <label for="attachment_path" class="form-label">Ganti Struk/Nota Pembelian (Opsional)</label>
                                @if($purchase->attachment_path)
                                    <p class="small text-muted mb-1">File saat ini: <a href="{{ asset('storage/' . $purchase->attachment_path) }}" target="_blank">Lihat file</a></p>
                                @endif
                                <input class="form-control" type="file" id="attachment_path" name="attachment_path">
                            </div>
                            <hr class="my-4">
                            <h5 class="mb-3">Detail Pembayaran</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Tunai" @selected(old('payment_method', $purchase->payment_method) == 'Tunai')>Tunai</option>
                                        <option value="Transfer Bank" @selected(old('payment_method', $purchase->payment_method) == 'Transfer Bank')>Transfer Bank</option>
                                        <option value="Lainnya" @selected(old('payment_method', $purchase->payment_method) == 'Lainnya')>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="payment_status" class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_status" x-model="payment_status" required>
                                        <option value="Lunas">Lunas</option>
                                        <option value="Belum Lunas">Belum Lunas</option>
                                    </select>
                                </div>
                                <template x-if="payment_status === 'Belum Lunas'">
                                    <div class="col-md-4 mb-3">
                                        <label for="amount_paid" class="form-label">Jumlah Dibayar (DP)</label>
                                        <input type="number" class="form-control" name="amount_paid" x-model.number="amount_paid" min="0">
                                    </div>
                                </template>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('karung.purchases.index') }}" class="btn btn-outline-secondary me-2"><i class="bi bi-x-circle"></i> Batal</a>
                                <button type="submit" class="btn btn-warning" :disabled="items.length === 0 || items.some(item => !item.product_id)"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-module-layout>

    @php
        $transactionProducts = $purchase->details->map(fn($detail) => $detail->product)->filter();
        $transactionProductIds = $transactionProducts->pluck('id');
        $additionalProducts = $products->whereNotIn('id', $transactionProductIds);
        $allProducts = $transactionProducts->concat($additionalProducts);
        $productsJson = $allProducts->map(function($product) {
            return [
                'value' => $product->id,
                'text' => $product->name,
                'purchase_price' => $product->purchase_price,
            ];
        });
    @endphp
    <x-slot name="scripts">
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('purchaseForm', (config) => ({
                    items: config.initialItems.length > 0 ? config.initialItems : [{ product_id: '', quantity: 1, price: 0 }],
                    productsData: config.productsData,
                    tomSelectInstances: [],
                    payment_status: config.payment_status || 'Lunas',
                    amount_paid: config.amount_paid || 0,
                    initTomSelect(element, index) {
                        const tomSelect = new TomSelect(element, {
                            options: productsData,
                            placeholder: '-- Pilih Produk --',
                            maxItems: 1,
                            onChange: (value) => { this.productChanged(index, value); }
                        });
                        this.tomSelectInstances[index] = tomSelect;

                        // [FIX-3] Gunakan $nextTick untuk memastikan TomSelect siap
                        this.$nextTick(() => {
                            const initialProductId = this.items[index].product_id;
                            if (initialProductId) {
                                tomSelect.setValue(initialProductId, 'silent');
                            }
                        });
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
                        if (this.tomSelectInstances[index]) this.tomSelectInstances[index].destroy();
                        this.items.splice(index, 1);
                        this.tomSelectInstances.splice(index, 1);
                    },
                    get total() { return this.items.reduce((sum, item) => sum + ( (item.quantity || 0) * (item.price || 0) ), 0); },
                    formatCurrency(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0); }
                }));
            });
            const productsData = @json($productsJson);
        </script>
    </x-slot>
</x-app-layout>