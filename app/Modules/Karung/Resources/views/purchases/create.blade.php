{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Catat Transaksi Pembelian Baru
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <div x-data="purchaseForm({
                productsData: productsData,
                payment_status: '{{ old('payment_status', 'Lunas') }}',
                amount_paid: '{{ old('amount_paid', 0) }}'
            })"
                 @product-selected-from-gallery.window="handleProductSelection($event.detail)">

                <form action="{{ route('karung.purchases.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Formulir Transaksi Pembelian</h5>
                        </div>
                        <div class="card-body">
                            {{-- Form Fields --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select" name="supplier_id">
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
                                    <input type="text" class="form-control @error('purchase_reference_no') is-invalid @enderror" name="purchase_reference_no" value="{{ old('purchase_reference_no') }}">
                                    @error('purchase_reference_no')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control" name="notes" rows="1">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                            <div class="p-3 border rounded mb-4 bg-light">
                                <h6 class="mb-3">Biaya Terkait Transaksi (Opsional)</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="form-label">Keterangan Biaya</label>
                                        <input type="text" class="form-control" name="related_expense_description" placeholder="Contoh: Biaya Kuli Angkut, Parkir">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jumlah Biaya (Rp)</label>
                                        <input type="number" class="form-control" name="related_expense_amount" placeholder="Contoh: 20000">
                                    </div>
                                </div>
                            </div>

                            {{-- Details Table --}}
                            <h5 class="mb-3">Detail Produk</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Produk <span class="text-danger">*</span> <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#productGalleryModal"><i class="bi bi-images"></i> Pilih dari Galeri</button></th>
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
                                        <tr><td colspan="5"><button type="button" @click="addItem()" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Tambah Baris</button></td></tr>
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

                            {{-- Attachment & Payment --}}
                            <div class="mb-3 mt-3">
                                <label for="attachment_path" class="form-label">Upload Struk/Nota Pembelian (Opsional)</label>
                                <input class="form-control" type="file" id="attachment_path" name="attachment_path">
                            </div>
                            <hr class="my-4">
                            <h5 class="mb-3">Detail Pembayaran</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Tunai" @selected(old('payment_method') == 'Tunai')>Tunai</option>
                                        <option value="Transfer Bank" @selected(old('payment_method') == 'Transfer Bank')>Transfer Bank</option>
                                        <option value="Lainnya" @selected(old('payment_method') == 'Lainnya')>Lainnya</option>
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
                                <a href="{{ route('karung.purchases.index') }}" class="btn btn-outline-secondary me-2"><i class="bi bi-x-circle"></i> Batal</a>
                                <button type="submit" class="btn btn-primary" :disabled="items.length === 0 || items.some(item => !item.product_id)"><i class="bi bi-check-circle-fill"></i> Simpan Transaksi</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="modal fade" id="productGalleryModal" tabindex="-1" aria-labelledby="productGalleryModalLabel" aria-hidden="true" x-data="productGallery()" x-init="init()">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        {{-- ... Isi Modal tidak berubah, bisa disalin dari kode sales/create ... --}}
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>

    @php
        $productsJson = $products->map(function($product) {
            return [
                'value' => $product->id,
                'text' => $product->name,
                'purchase_price' => $product->purchase_price,
            ];
        });
    @endphp
    <x-slot name="scripts">
        <style> .product-card:hover { transform: scale(1.05); transition: transform 0.2s; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); } </style>
        <script>
            const productsData = @json($productsJson);
            document.addEventListener('alpine:init', () => {
                Alpine.data('productGallery', () => ({
                    products: [], isLoading: false, searchTerm: '', currentPage: 1, hasMorePages: true, galleryModal: null,
                    init() { this.galleryModal = new bootstrap.Modal(document.getElementById('productGalleryModal')); document.getElementById('productGalleryModal').addEventListener('show.bs.modal', () => { this.handleSearch(); }); },
                    fetchProducts() { if(this.isLoading) return; this.isLoading = true; const url = `{{ route('karung.products.gallery.api') }}?page=${this.currentPage}&search=${this.searchTerm}`; fetch(url).then(res => res.json()).then(data => { this.products.push(...data.data); this.hasMorePages = data.next_page_url !== null; }).catch(err => console.error(err)).finally(() => this.isLoading = false); },
                    handleSearch() { this.products = []; this.currentPage = 1; this.hasMorePages = true; this.fetchProducts(); },
                    loadMore() { if (this.hasMorePages) { this.currentPage++; this.fetchProducts(); } },
                    selectProduct(product) { window.dispatchEvent(new CustomEvent('product-selected-from-gallery', { detail: product })); this.galleryModal.hide(); },
                    formatCurrency(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); }
                }));

                Alpine.data('purchaseForm', (config) => {
                    let oldDetails = @json(old('details')) || [];
                    return {
                        items: oldDetails.length > 0 ? oldDetails : [{ product_id: '', quantity: 1, price: 0 }],
                        tomSelectInstances: [],
                        payment_status: config.payment_status || 'Lunas',
                        amount_paid: config.amount_paid || 0,
                        initTomSelect(element, index) {
                            const tomSelect = new TomSelect(element, {
                                options: config.productsData,
                                placeholder: '-- Pilih atau Cari Produk --',
                                maxItems: 1,
                                onChange: (value) => this.productChanged(index, value),
                            });
                            this.tomSelectInstances[index] = tomSelect;
                            if(this.items[index].product_id) {
                                tomSelect.setValue(this.items[index].product_id, true);
                            }
                        },
                        productChanged(index, selectedProductId) {
                            this.items[index].product_id = selectedProductId;
                            const selectedProduct = config.productsData.find(p => p.value == selectedProductId);
                            if (selectedProduct) { this.items[index].price = selectedProduct.purchase_price; }
                            else { this.items[index].price = 0; }
                        },
                        addItem() { this.items.push({ product_id: '', quantity: 1, price: 0 }); },
                        removeItem(index) {
                            if (this.tomSelectInstances[index]) { this.tomSelectInstances[index].destroy(); }
                            this.items.splice(index, 1);
                            this.tomSelectInstances.splice(index, 1);
                        },
                        handleProductSelection(product) {
                            let targetIndex = this.items.findIndex(item => !item.product_id);
                            if (targetIndex === -1) { this.addItem(); targetIndex = this.items.length - 1; }
                            this.$nextTick(() => {
                                this.items[targetIndex].product_id = product.id;
                                this.items[targetIndex].price = product.purchase_price;
                                const tomSelect = this.tomSelectInstances[targetIndex];
                                if (tomSelect) {
                                    if (!tomSelect.getOption(product.id)) {
                                        tomSelect.addOption({ value: product.id, text: product.name });
                                    }
                                    tomSelect.setValue(product.id, false);
                                }
                            });
                        },
                        get total() { return this.items.reduce((sum, item) => sum + ( (item.quantity || 0) * (item.price || 0) ), 0); },
                        formatCurrency(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0); }
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>