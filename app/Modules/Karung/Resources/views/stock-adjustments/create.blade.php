@extends('karung::layouts.karung_app')

@section('title', 'Buat Penyesuaian Stok Baru')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            {{-- [MODIFIKASI] Tambahkan x-data untuk menginisialisasi Alpine.js --}}
            <div class="card" x-data="adjustmentForm({ products: {{ $products->map(fn($p) => ['id' => $p->id, 'stock' => $p->stock]) }} })">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Formulir Penyesuaian Stok (Stok Opname)</h5>
                </div>
                
                <form action="{{ route('karung.stock-adjustments.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('karung::components.flash-message')

                        <div class="mb-3">
                            <label for="product_id" class="form-label">Produk yang Disesuaikan <span class="text-danger">*</span></label>
                            {{-- [MODIFIKASI] Beri x-ref agar bisa dijangkau oleh Alpine --}}
                            <select id="product_id" name="product_id" required x-ref="productSelect" @change="productChanged($event)">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- [MODIFIKASI] Tampilan info stok yang dinamis --}}
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
                                    {{-- [MODIFIKASI] Tambahkan x-model untuk melacak tipe --}}
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
                                    {{-- [MODIFIKASI] Label dinamis --}}
                                    <label for="quantity" class="form-label" x-text="quantityLabel"></label>
                                    {{-- [MODIFIKASI] Tambahkan x-model untuk melacak nilai --}}
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
                        <a href="{{ route('karung.stock-adjustments.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v6h-2a.5.5 0 0 0-.354.854l2.5 2.5a.5.5 0 0 0 .708 0l2.5-2.5A.5.5 0 0 0 10.5 7.5h-2z"/></svg>
                            Simpan Penyesuaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- [BARU] Menambahkan PUSH untuk script di footer --}}
@push('footer-scripts')
<script>
function adjustmentForm(config) {
    return {
        products: config.products || [],
        selectedProductId: null,
        currentStock: '-',
        adjustmentType: 'Stok Opname',
        adjustmentValue: null,
        tomSelect: null,

        // Inisialisasi TomSelect pada dropdown produk
        init() {
            this.tomSelect = new TomSelect(this.$refs.productSelect, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        },

        // Dipanggil saat produk di dropdown berubah
        productChanged(event) {
            this.selectedProductId = event.target.value;
            if (this.selectedProductId) {
                const product = this.products.find(p => p.id == this.selectedProductId);
                this.currentStock = product ? product.stock : '-';
            } else {
                this.currentStock = '-';
            }
        },

        // Mengubah label berdasarkan Tipe Penyesuaian
        get quantityLabel() {
            switch(this.adjustmentType) {
                case 'Stok Opname':
                    return 'Jumlah Fisik Sebenarnya *';
                case 'Barang Rusak':
                case 'Barang Hilang':
                    return 'Jumlah Barang yang Rusak/Hilang *';
                case 'Koreksi Data':
                    return 'Nilai Koreksi (+/-) *';
                default:
                    return 'Jumlah Penyesuaian *';
            }
        },
        
        // Mengubah teks bantuan berdasarkan Tipe Penyesuaian
        get quantityHelpText() {
            switch(this.adjustmentType) {
                case 'Stok Opname':
                    return 'Masukkan jumlah fisik hasil hitungan di gudang.';
                case 'Barang Rusak':
                case 'Barang Hilang':
                    return 'Masukkan jumlah positif (misal: 5 untuk 5 barang rusak).';
                case 'Koreksi Data':
                     return 'Masukkan nilai positif untuk menambah, negatif untuk mengurangi.';
                default:
                    return 'Masukkan jumlah yang disesuaikan.';
            }
        },

        // Menghitung dan menampilkan stok baru secara dinamis
        get newStockDisplay() {
            if (this.currentStock === '-' || this.adjustmentValue === null) {
                return '-';
            }
            let newStock = 0;
            const current = parseInt(this.currentStock);
            const value = parseInt(this.adjustmentValue);

            switch(this.adjustmentType) {
                case 'Stok Opname':
                    newStock = value;
                    break;
                case 'Barang Rusak':
                case 'Barang Hilang':
                    newStock = current - Math.abs(value); // Pastikan nilainya mengurangi
                    break;
                case 'Koreksi Data':
                    newStock = current + value;
                    break;
                default:
                     newStock = current + value;
            }
            return isNaN(newStock) ? '-' : newStock;
        }
    }
}
</script>
@endpush