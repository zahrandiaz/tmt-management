@extends('karung::layouts.karung_app')

@section('title', 'Catat Transaksi Pembelian Baru - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    {{-- [MODIFIKASI] Menambahkan x-data dan event listener untuk galeri --}}
    <div x-data="purchaseForm({ productsData: productsData, payment_status: '{{ old('payment_status', 'Lunas') }}', amount_paid: '{{ old('amount_paid', 0) }}' })"
         @product-selected-from-gallery.window="handleProductSelection($event.detail)">

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
                                            <th scope="col" style="width: 40%;">
                                                Produk <span class="text-danger">*</span>
                                                <!-- [BARU] Tombol untuk membuka galeri modal -->
                                                <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#productGalleryModal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16"><path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/></svg>
                                                    Pilih dari Galeri
                                                </button>
                                            </th>
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

    <!-- [BARU] Modal untuk Galeri Produk -->
    <div class="modal fade" id="productGalleryModal" tabindex="-1" aria-labelledby="productGalleryModalLabel" aria-hidden="true" x-data="productGallery()" x-init="init()">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productGalleryModalLabel">Galeri Produk</h5>
                    <div class="ms-auto w-50">
                        <input type="text" class="form-control" placeholder="Cari nama atau SKU produk..." x-model="searchTerm" @input.debounce.500ms="handleSearch()">
                    </div>
                </div>
                <div class="modal-body">
                    <template x-if="isLoading && products.length === 0">
                        <div class="d-flex justify-content-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
                    </template>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3" id="gallery-content">
                        <template x-for="(product, index) in products" :key="`${product.id}-${index}`">
                            <div class="col">
                                <div class="card h-100 product-card" @click="selectProduct(product)" style="cursor: pointer;">
                                    <img :src="product.image_url" class="card-img-top" style="height: 150px; object-fit: cover;" :alt="product.name" onerror="this.onerror=null;this.src='https://via.placeholder.com/150';">
                                    <div class="card-body p-2">
                                        <h6 class="card-title" x-text="product.name"></h6>
                                        <p class="card-text mb-1" x-text="formatCurrency(product.purchase_price)"></p>
                                        <p class="card-text"><span class="badge bg-secondary">Stok: <span x-text="product.stock"></span></span></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <div x-show="isLoading && products.length > 0" class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <button type="button" class="btn btn-primary" @click="loadMore()" x-show="!isLoading && hasMorePages">Muat Lebih Banyak...</button>
                    <p x-show="!isLoading && !hasMorePages && products.length > 0" class="text-muted mb-0">-- Akhir dari daftar --</p>
                    <p x-show="!isLoading && products.length === 0" class="text-muted mb-0">Produk tidak ditemukan.</p>
                </div>
            </div>
        </div>
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
<style>
    .product-card:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease-in-out;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>
<script>
    // [REUSE] Fungsi productGallery sama persis seperti di halaman penjualan
    function productGallery() {
        return {
            products: [], isLoading: false, searchTerm: '', currentPage: 1, hasMorePages: true, galleryModal: null,
            init() {
                this.galleryModal = new bootstrap.Modal(document.getElementById('productGalleryModal'));
                document.getElementById('productGalleryModal').addEventListener('show.bs.modal', () => { this.handleSearch(); });
            },
            fetchProducts() {
                if(this.isLoading) return; this.isLoading = true;
                const url = `{{ route('karung.products.gallery.api') }}?page=${this.currentPage}&search=${this.searchTerm}`;
                fetch(url).then(res => res.json()).then(data => {
                    this.products.push(...data.data);
                    this.hasMorePages = data.next_page_url !== null;
                }).catch(err => console.error(err)).finally(() => this.isLoading = false);
            },
            handleSearch() { this.products = []; this.currentPage = 1; this.hasMorePages = true; this.fetchProducts(); },
            loadMore() { if (this.hasMorePages) { this.currentPage++; this.fetchProducts(); } },
            selectProduct(product) { window.dispatchEvent(new CustomEvent('product-selected-from-gallery', { detail: product })); this.galleryModal.hide(); },
            formatCurrency(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); }
        }
    }

    // [MODIFIKASI] Sesuaikan purchaseForm dengan logika galeri
    function purchaseForm(config) {
        return {
            items: [{ product_id: '', quantity: 1, price: 0 }],
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
            },
            
            productChanged(index, selectedProductId) {
                this.items[index].product_id = selectedProductId;
                const selectedProduct = config.productsData.find(p => p.value == selectedProductId);
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
                if (isNaN(value)) { return 'Rp 0'; }
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            },

            // [BARU] Method untuk menangani produk yang dipilih dari galeri
            handleProductSelection(product) {
                let targetIndex = this.items.findIndex(item => !item.product_id);
                if (targetIndex === -1) {
                    this.addItem();
                    targetIndex = this.items.length - 1;
                }
                
                this.$nextTick(() => {
                    this.items[targetIndex].product_id = product.id;
                    this.items[targetIndex].price = product.purchase_price; // Gunakan purchase_price
                    
                    const tomSelect = this.tomSelectInstances[targetIndex];
                    if (tomSelect) {
                        if (!tomSelect.getOption(product.id)) {
                            tomSelect.addOption({ value: product.id, text: product.name });
                        }
                        tomSelect.setValue(product.id, false);
                    }
                });
            }
        }
    }
</script>
@endpush
