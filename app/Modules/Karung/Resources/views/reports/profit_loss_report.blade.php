{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Laporan Laba Rugi
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Laporan Laba Rugi</h5>
                            <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Dashboard</a>
                        </div>
                        <div class="card-body">
                            {{-- Filter Presets --}}
                            <div class="mb-3">
                                <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'today']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                                <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_week']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                                <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_month']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                                <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_year']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                            </div>

                            {{-- Form Filter Manual --}}
                            <form method="GET" action="{{ route('karung.reports.profit_and_loss') }}" class="mb-4 p-3 border rounded">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                                    </div>
                                </div>
                            </form>

                            {{-- Tombol Export --}}
                            <div class="mb-4">
                                <strong>Export Laporan:</strong>
                                @php
                                    $exportParams = array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate]);
                                @endphp
                                <a href="{{ route('karung.reports.profit_loss.export', $exportParams) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel-fill"></i> Excel</a>
                                <a href="{{ route('karung.reports.profit_loss.export.pdf', $exportParams) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</a>
                            </div>
                            <hr>

                            {{-- [MODIFIKASI TOTAL v1.32.0] Ringkasan Laporan dengan Rincian HPP --}}
                            <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}</h5>
                            
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <table class="table table-sm table-borderless">
                                        <tbody>
                                            <tr class="table-light"><td colspan="2" class="fw-bold">PENDAPATAN</td></tr>
                                            <tr>
                                                <td class="ps-3">Pendapatan Kotor (Omzet)</td>
                                                <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">(-) Retur Penjualan</td>
                                                <td class="text-end text-danger">(-Rp {{ number_format($totalSalesReturns, 0, ',', '.') }})</td>
                                            </tr>
                                            <tr class="table-secondary">
                                                <td class="fw-bold">(=) PENDAPATAN BERSIH</td>
                                                <td class="text-end fw-bold">Rp {{ number_format($netRevenue, 0, ',', '.') }}</td>
                                            </tr>
                                            
                                            <tr class="table-light mt-3"><td colspan="2" class="fw-bold">BEBAN POKOK PENJUALAN (HPP)</td></tr>
                                            <tr>
                                                <td class="ps-3">HPP dari Penjualan</td>
                                                <td class="text-end">Rp {{ number_format($totalCostOfGoodsSold, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">(-) Pengembalian HPP dari Retur Jual</td>
                                                <td class="text-end text-danger">(-Rp {{ number_format($costOfReturnedGoods, 0, ',', '.') }})</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">(-) Nilai Retur Pembelian</td>
                                                <td class="text-end text-danger">(-Rp {{ number_format($totalPurchaseReturns, 0, ',', '.') }})</td>
                                            </tr>
                                            <tr class="table-secondary">
                                                <td class="fw-bold">(=) HPP BERSIH</td>
                                                <td class="text-end fw-bold">Rp {{ number_format($netCostOfGoodsSold, 0, ',', '.') }}</td>
                                            </tr>

                                            <tr class="table-info">
                                                <td class="fw-bolder">(=) LABA KOTOR (PENDAPATAN BERSIH - HPP BERSIH)</td>
                                                <td class="text-end fw-bolder fs-5">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                                            </tr>
                                            
                                            <tr class="table-light mt-3"><td colspan="2" class="fw-bold">BIAYA OPERASIONAL</td></tr>
                                             <tr>
                                                <td class="ps-3">(-) Total Biaya</td>
                                                <td class="text-end text-danger">(-Rp {{ number_format($totalExpenses, 0, ',', '.') }})</td>
                                            </tr>
                                            
                                            <tr class="{{ $netProfit >= 0 ? 'table-primary' : 'table-danger' }}">
                                                <td class="fw-bolder">(=) LABA BERSIH (LABA KOTOR - BIAYA)</td>
                                                <td class="text-end fw-bolder fs-4">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-lg-5 align-self-center">
                                    <div style="height: 350px;"><canvas id="profitChart"></canvas></div>
                                </div>
                            </div>
                            <hr>
                            
                            {{-- Tabel Detail --}}
                            <h5 class="mb-3 mt-4">Rincian Laba per Item Terjual</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tanggal</th><th>Invoice</th><th>Produk</th><th class="text-center">Jml</th>
                                            <th class="text-end">Harga Jual</th><th class="text-end">HPP/item</th>
                                            <th class="text-end">Laba/item</th><th class="text-end">Subtotal Laba</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($salesDetails as $detail)
                                        @php
                                            $purchasePrice = $detail->purchase_price_at_sale; 
                                            $profitPerItem = $detail->selling_price_at_transaction - $purchasePrice;
                                            $subTotalProfit = $detail->quantity * $profitPerItem;
                                        @endphp
                                            <tr>
                                                <td>{{ $detail->transaction->transaction_date->format('d-m-Y') }}</td>
                                                <td>{{ $detail->transaction->invoice_number }}</td>
                                                <td>{{ $detail->product?->name ?: 'Produk Telah Dihapus' }}</td>
                                                <td class="text-center">{{ $detail->quantity }}</td>
                                                <td class="text-end">Rp {{ number_format($detail->selling_price_at_transaction, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($purchasePrice, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($profitPerItem, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold {{ $subTotalProfit < 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($subTotalProfit, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center">Tidak ada data penjualan pada rentang tanggal yang dipilih.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>
    
    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('profitChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Laba Bersih', 'HPP Bersih', 'Biaya Operasional'],
                            datasets: [{
                                data: [
                                    {{ $netProfit > 0 ? $netProfit : 0 }}, 
                                    {{ $netCostOfGoodsSold > 0 ? $netCostOfGoodsSold : 0 }},
                                    {{ $totalExpenses > 0 ? $totalExpenses : 0 }}
                                ],
                                backgroundColor: [
                                    'rgba(13, 110, 253, 0.8)',
                                    'rgba(220, 53, 69, 0.8)',
                                    'rgba(255, 193, 7, 0.8)',
                                ],
                                borderColor: ['#FFFFFF'],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) { label += ': '; }
                                            if (context.raw !== null) {
                                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    </x-slot>
</x-app-layout>