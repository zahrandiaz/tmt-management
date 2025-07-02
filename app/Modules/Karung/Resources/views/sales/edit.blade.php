{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Edit Transaksi Penjualan: {{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <div x-data="salesForm({
                initialItems: {{ json_encode($sale->details->map(function($detail) {
                    if (!$detail->product) return null;
                    return [
                        'product_id' => $detail->product_id,
                        'quantity' => $detail->quantity,
                        'price' => $detail->selling_price_at_transaction,
                        'hpp' => $detail->purchase_price_at_sale,
                        'stock' => ($detail->product->stock ?? 0) + $detail->quantity,
                        'error' => ''
                    ];
                })->filter()) }},
                productsData: productsData,
                payment_status: '{{ old('payment_status', $sale->payment_status) }}',
                amount_paid: '{{ old('amount_paid', $sale->amount_paid) }}'
            })">
                <form action="{{ route('karung.sales.update', $sale->id) }}" method="POST" @submit="validateForm">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Edit Transaksi Penjualan: {{ $sale->invoice_number }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- Form Fields --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="transaction_date" value="{{ old('transaction_date', $sale->transaction_date->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">Pelanggan</label>
                                    <select class="form-select" name="customer_id" id="customer_id_select">
                                        <option value="">-- Penjualan Umum --</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected(old('customer_id', $sale->customer_id) == $customer->id)>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="notes" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" name="notes" rows="1">{{ old('notes', $sale->notes) }}</textarea>
                            </div>
                            @php $relatedExpense = $sale->operationalExpenses->first(); @endphp
                            <div class="p-3 border rounded mb-4 bg-light">
                                <h6 class="mb-3">Biaya Terkait Transaksi (Opsional)</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="form-label">Keterangan Biaya</label>
                                        <input type="text" class="form-control" name="related_expense_description" value="{{ old('related_expense_description', $relatedExpense->description ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jumlah Biaya (Rp)</label>
                                        <input type="number" class="form-control" name="related_expense_amount" value="{{ old('related_expense_amount', $relatedExpense->amount ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Details Table --}}
                            <h5 class="mb-3">Detail Produk</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 35%;">Produk <span class="text-danger">*</span></th>
                                            <th>Jumlah <span class="text-danger">*</span></th>
                                            <th>Harga Modal (HPP)</th>
                                            <th>Harga Jual <span class="text-danger">*</span></th>
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
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][quantity]'" x-model.number="item.quantity" @input="validateStock(index)" class="form-control" :class="{'is-invalid': item.error}" required min="1">
                                                    <template x-if="item.error"><div class="text-danger small mt-1" x-text="item.error"></div></template>
                                                </td>
                                                <td>
                                                    @can('karung.edit_historical_hpp')
                                                        <input type="number" :name="'details[' + index + '][purchase_price_at_sale]'" x-model.number="item.hpp" class="form-control" required min="0">
                                                    @else
                                                        <input type="text" :value="formatCurrency(item.hpp)" class="form-control bg-light" readonly>
                                                        <input type="hidden" :name="'details[' + index + '][purchase_price_at_sale]'" x-model="item.hpp">
                                                    @endcan
                                                </td>
                                                <td><input type="number" :name="'details[' + index + '][selling_price_at_transaction]'" x-model.number="item.price" class="form-control" required min="0"></td>
                                                <td><input type="text" :value="formatCurrency(item.quantity * item.price)" class="form-control bg-light" readonly></td>
                                                <td class="text-center"><button type="button" @click="removeItem(index)" class="btn btn-danger btn-sm">&times;</button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr><td colspan="6"><button type="button" @click="addItem()" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Tambah Baris</button></td></tr>
                                        <tr>
                                            <th colspan="4" class="text-end">TOTAL PENJUALAN:</th>
                                            <td colspan="2">
                                                <input type="text" :value="formatCurrency(total)" class="form-control fw-bold fs-5 text-end bg-light" readonly>
                                                <input type="hidden" name="total_amount" :value="total">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @error('details') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                            {{-- Payment Details --}}
                            <hr class="my-4">
                            <h5 class="mb-3">Detail Pembayaran</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Tunai" @selected(old('payment_method', $sale->payment_method) == 'Tunai')>Tunai</option>
                                        <option value="Transfer Bank" @selected(old('payment_method', $sale->payment_method) == 'Transfer Bank')>Transfer Bank</option>
                                        <option value="Lainnya" @selected(old('payment_method', $sale->payment_method) == 'Lainnya')>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_status" x-model="payment_status" required>
                                        <option value="Lunas">Lunas</option>
                                        <option value="Belum Lunas">Belum Lunas</option>
                                    </select>
                                </div>
                                <template x-if="payment_status === 'Belum Lunas'">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jumlah Dibayar (DP)</label>
                                        <input type="number" class="form-control" name="amount_paid" x-model.number="amount_paid" min="0">
                                    </div>
                                </template>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('karung.sales.index') }}" class="btn btn-outline-secondary me-2"><i class="bi bi-x-circle"></i> Batal</a>
                                <button type="submit" class="btn btn-warning" :disabled="items.length === 0 || items.some(item => !item.product_id || item.error)"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-module-layout>

    @php
        $transactionProducts = $sale->details->map(fn($detail) => $detail->product)->filter();
        $transactionProductIds = $transactionProducts->pluck('id');
        $additionalProducts = $products->whereNotIn('id', $transactionProductIds);
        $allProducts = $transactionProducts->concat($additionalProducts);
        $productsJson = $allProducts->map(function($product) use ($sale) {
            $detail = $sale->details->firstWhere('product_id', $product->id);
            $stockForDropdown = ($product->stock ?? 0) + ($detail ? $detail->quantity : 0);
            return [
                'value' => $product->id,
                'text' => $product->name . ' (Stok: ' . $stockForDropdown . ')',
                'selling_price' => $product->selling_price,
                'stock' => $stockForDropdown,
            ];
        });
    @endphp
    <x-slot name="scripts">
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('salesForm', (config) => ({
                    items: config.initialItems,
                    productsData: config.productsData,
                    tomSelectInstances: [],
                    payment_status: config.payment_status,
                    amount_paid: config.amount_paid,
                    initTomSelect(element, index) {
                        const tomSelect = new TomSelect(element, {
                            options: this.productsData,
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
                    productChanged(index, value) {
                        this.items[index].product_id = value;
                        const p = this.productsData.find(p => p.value == value);
                        if(p){ this.items[index].price = p.selling_price; this.items[index].stock = p.stock; }
                        else { this.items[index].price = 0; this.items[index].stock = Infinity; }
                        this.validateStock(index);
                    },
                    validateStock(index) {
                        const item = this.items[index];
                        if (item.quantity > item.stock) { item.error = `Stok tdk cukup (sisa ${item.stock})`; }
                        else { item.error = ''; }
                    },
                    validateForm(e) { /* ... (Fungsi validasi tidak berubah) ... */ },
                    addItem() { this.items.push({ product_id: '', quantity: 1, price: 0, hpp: 0, stock: Infinity, error: '' }); },
                    removeItem(index) {
                        if (this.tomSelectInstances[index]) this.tomSelectInstances[index].destroy();
                        this.items.splice(index, 1);
                        this.tomSelectInstances.splice(index, 1);
                    },
                    get total() { return this.items.reduce((sum, item) => sum + ((item.quantity||0) * (item.price||0)), 0); },
                    formatCurrency(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0); }
                }));
            });
            const productsData = @json($productsJson);
        </script>
    </x-slot>
</x-app-layout>