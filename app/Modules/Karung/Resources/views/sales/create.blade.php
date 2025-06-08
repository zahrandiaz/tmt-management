@extends('layouts.tmt_app')

@section('title', 'Catat Transaksi Penjualan Baru - Modul Toko Karung')

@section('content')
<div class="container-fluid">
    {{-- Kita bungkus semua dalam satu komponen Alpine.js --}}
    <div x-data="salesForm()">
        <form action="{{ route('karung.sales.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Catat Transaksi Penjualan Baru</h5>
                        </div>
                        <div class="card-body">
                            {{-- Baris 1: Tanggal & Pelanggan --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">Pelanggan</label>
                                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                        <option value="">-- Penjualan Umum / Tanpa Pelanggan --</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Baris 2: Catatan --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Bagian Detail Produk Penjualan --}}
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
                                        {{-- Loop dinamis menggunakan Alpine.js --}}
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td>
                                                    <select :name="'details[' + index + '][product_id]'" x-model="item.product_id" @change="productChanged(index)" class="form-select" required>
                                                        <option value="">-- Pilih Produk --</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }} (Stok: {{ $product->stock }})</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][quantity]'" x-model.number="item.quantity" class="form-control" placeholder="Jumlah" required min="1">
                                                </td>
                                                <td>
                                                    <input type="number" :name="'details[' + index + '][selling_price_at_transaction]'" x-model.number="item.price" class="form-control" placeholder="Harga Jual" required min="0">
                                                </td>
                                                <td>
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
                                            <th colspan="3" class="text-end">TOTAL PENJUALAN:</th>
                                            <td colspan="2">
                                                <input type="text" x-model="formatCurrency(total)" class="form-control fw-bold fs-5 text-end" readonly>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('karung.sales.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-success">Simpan Transaksi Penjualan</button>
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
    function salesForm() {
        // Kita ubah data produk dari PHP ke format JSON agar bisa dibaca JavaScript
        const products = @json($products->map(function($product) {
            return [
                'id' => $product->id,
                'selling_price' => $product->selling_price,
            ];
        })->keyBy('id'));

        return {
            items: [{ product_id: '', quantity: 1, price: 0 }], // Mulai dengan 1 baris kosong

            productChanged(index) {
                const selectedProductId = this.items[index].product_id;
                if (selectedProductId && products[selectedProductId]) {
                    // Jika produk dipilih, otomatis isi harga jualnya dari data master produk
                    this.items[index].price = products[selectedProductId].selling_price;
                } else {
                    this.items[index].price = 0;
                }
            },

            addItem() {
                this.items.push({
                    product_id: '',
                    quantity: 1,
                    price: 0
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            get total() {
                return this.items.reduce((sum, item) => {
                    const quantity = isNaN(item.quantity) ? 0 : item.quantity;
                    const price = isNaN(item.price) ? 0 : item.price;
                    return sum + (quantity * price);
                }, 0);
            },

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