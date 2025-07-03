{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Buat Penyesuaian Stok Baru
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card" x-data="adjustmentForm({ products: {{ json_encode($products->map(fn($p) => ['id' => $p->id, 'stock' => $p->stock])) }} })">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Formulir Penyesuaian Stok (Stok Opname)</h5>
                        </div>
                        
                        <form action="{{ route('karung.stock-adjustments.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                @include('karung::components.flash-message')

                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Produk yang Disesuaikan <span class="text-danger">*</span></label>
                                    <select id="product_id_select" name="product_id" required x-ref="productSelect">
                                        {{-- Opsi akan diisi oleh TomSelect --}}
                                    </select>
                                </div>

                                {{-- Tampilan info stok dinamis --}}
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="alert alert-secondary">
                                            <strong>Stok Sistem Saat Ini:</strong> 
                                            <strong class="fs-5" x-text="currentStock"></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="alert alert-success">
                                            <strong>Stok Setelah Penyesuaian:</strong> 
                                            <strong class="fs-5" x-text="newStockDisplay"></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Tipe Penyesuaian <span class="text-danger">*</span></label>
                                            <select class="form-select" id="type" name="type" required x-model="adjustmentType">
                                                <option value="Stok Opname">Stok Opname</option>
                                                <option value="Barang Rusak">Barang Rusak</option>
                                                <option value="Barang Hilang">Barang Hilang</option>
                                                <option value="Koreksi Data">Koreksi Data</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label" x-text="quantityLabel"></label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" required placeholder="Masukkan jumlah..." x-model.number="adjustmentValue">
                                            <small class="form-text text-muted" x-text="quantityHelpText"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label">Alasan Penyesuaian <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Contoh: Hasil stok opname bulanan, ditemukan 2 barang rusak, dll."></textarea>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <a href="{{ route('karung.stock-adjustments.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save-fill"></i> Simpan Penyesuaian
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('adjustmentForm', (config) => ({
                    products: config.products || [],
                    selectedProductId: null,
                    currentStock: '-',
                    adjustmentType: 'Stok Opname',
                    adjustmentValue: null,
                    tomSelect: null,
                    init() {
                        this.tomSelect = new TomSelect(this.$refs.productSelect, {
                            options: @json($products->map(fn($p) => ['value' => $p->id, 'text' => $p->name])),
                            placeholder: '-- Pilih Produk --',
                            create: false,
                            sortField: { field: "text", direction: "asc" },
                            onChange: (value) => { this.productChanged(value); }
                        });
                    },
                    productChanged(value) {
                        this.selectedProductId = value;
                        if (this.selectedProductId) {
                            const product = this.products.find(p => p.id == this.selectedProductId);
                            this.currentStock = product ? product.stock : '-';
                        } else {
                            this.currentStock = '-';
                        }
                    },
                    get quantityLabel() {
                        switch(this.adjustmentType) {
                            case 'Stok Opname': return 'Jumlah Fisik Sebenarnya *';
                            case 'Barang Rusak':
                            case 'Barang Hilang': return 'Jumlah Barang yang Rusak/Hilang *';
                            case 'Koreksi Data': return 'Nilai Koreksi (+/-) *';
                            default: return 'Jumlah Penyesuaian *';
                        }
                    },
                    get quantityHelpText() {
                        switch(this.adjustmentType) {
                            case 'Stok Opname': return 'Masukkan jumlah fisik hasil hitungan di gudang.';
                            case 'Barang Rusak':
                            case 'Barang Hilang': return 'Masukkan jumlah positif (misal: 5 untuk 5 barang rusak).';
                            case 'Koreksi Data': return 'Masukkan nilai positif untuk menambah, negatif untuk mengurangi.';
                            default: return 'Masukkan jumlah yang disesuaikan.';
                        }
                    },
                    get newStockDisplay() {
                        if (this.currentStock === '-' || this.adjustmentValue === null) return '-';
                        let newStock = 0;
                        const current = parseInt(this.currentStock);
                        const value = parseInt(this.adjustmentValue);
                        if (isNaN(current) || isNaN(value)) return '-';

                        switch(this.adjustmentType) {
                            case 'Stok Opname': newStock = value; break;
                            case 'Barang Rusak':
                            case 'Barang Hilang': newStock = current - Math.abs(value); break;
                            case 'Koreksi Data': newStock = current + value; break;
                            default: newStock = current + value;
                        }
                        return isNaN(newStock) ? '-' : newStock;
                    }
                }));
            });
        </script>
    </x-slot>
</x-app-layout>