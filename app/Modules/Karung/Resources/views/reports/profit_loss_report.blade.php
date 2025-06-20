@extends('karung::layouts.karung_app')

@section('title', 'Laporan Laba Rugi - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Laporan Laba Rugi</h5>
                    <a href="{{ route('karung.dashboard') }}" class="btn btn-light btn-sm">Kembali ke Dashboard</a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'today']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'today' ? 'active' : '' }}">Hari Ini</a>
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_week']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_week' ? 'active' : '' }}">Minggu Ini</a>
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_month']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                        <a href="{{ route('karung.reports.profit_and_loss', ['preset' => 'this_year']) }}" class="btn btn-outline-primary btn-sm {{ $activePreset == 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                    </div>

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
                                <button type="submit" class="btn btn-primary w-100">Filter Manual</button>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4">
                        <strong>Export Laporan:</strong>
                        {{-- [MODIFIKASI] Menambahkan parameter tanggal ke route export --}}
                        @php
                            $exportParams = array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate]);
                        @endphp
                        <a href="{{ route('karung.reports.profit_loss.export', $exportParams) }}" class="btn btn-success btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Excel</a>
                        <a href="{{ route('karung.reports.profit_loss.export.pdf', $exportParams) }}" class="btn btn-danger btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf-fill" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572a.27.27 0 0 1-.035.012.28.28 0 0 1-.031-.023c-.075-.041-.158-.1-.218-.17a.85.85 0 0 1-.135-.37c-.014-.042-.027-.102-.038-.172a.21.21 0 0 1 .035-.145c.022-.02.05-.038.083-.051a.2.2 0 0 1 .051-.028.2.2 0 0 1 .068.004q.032.007.07.02z"/><path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM.5 11.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/></svg> PDF</a>
                    </div>
                    <hr>

                    <h5 class="mb-3">Ringkasan Laporan untuk Periode {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Akhir' }}</h5>
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-md-4">
                            <div style="height: 250px;"><canvas id="profitChart"></canvas></div>
                        </div>
                        {{-- [MODIFIKASI] Menambahkan Biaya Operasional dan Laba Bersih --}}
                        <div class="col-md-8">
                            <div class="card border-success border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(+) Total Pendapatan (Omzet)</span> <span class="fw-bold fs-5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card border-secondary border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(-) Total Modal Terjual (HPP)</span> <span class="fw-bold fs-5">Rp {{ number_format($totalCost, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card bg-light border-secondary border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(=) Laba Kotor</span> <span class="fw-bold fs-5">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span></div>
                            </div></div>
                             <div class="card border-danger border-2 mb-2"><div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center"><span>(-) Total Biaya Operasional</span> <span class="fw-bold fs-5">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span></div>
                            </div></div>
                            <div class="card text-white {{ $netProfit >= 0 ? 'bg-primary' : 'bg-danger' }}"><div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center"><h6 class="card-title mb-0">(=) LABA BERSIH</h6><p class="card-text fs-4 fw-bold mb-0">Rp {{ number_format($netProfit, 0, ',', '.') }}</p></div>
                            </div></div>
                        </div>
                    </div>
                    <hr>

                    <h5 class="mb-3">Ringkasan Laba per Kategori Produk</h5>
                    <div class="table-responsive mb-5">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Kategori Produk</th>
                                    <th class="text-end">Total Laba Kotor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($profitByCategory as $item)
                                    <tr>
                                        <td>{{ $item['category_name'] }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($item['total_profit'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">Tidak ada data laba per kategori untuk periode ini.</td></tr>
                                @endforelse
                                <tr class="table-dark">
                                    <td class="fw-bold">TOTAL LABA KOTOR</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mb-3">Rincian Laba per Item Terjual</h5>
                    <div class="table-responsive">
                        {{-- Tabel rincian per item tidak saya ubah, hanya memastikan datanya masih benar --}}
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th><th>Invoice</th><th>Produk</th><th class="text-center">Jml</th>
                                    <th class="text-end">Harga Jual</th><th class="text-end">Harga Beli (Ref.)</th>
                                    <th class="text-end">Laba per Item</th><th class="text-end">Subtotal Laba</th>
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
@endsection

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('profitChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Laba Kotor', 'Total Modal (HPP)'],
                    datasets: [{
                        data: [{{ $grossProfit > 0 ? $grossProfit : 0 }}, {{ $totalCost > 0 ? $totalCost : 0 }}],
                        // [PERBAIKAN] Mengubah warna agar sesuai kartu: Laba (Biru), Modal (Merah)
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.8)', // Primary color for profit
                            'rgba(220, 53, 69, 0.8)',  // Danger color for cost
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(220, 53, 69, 1)',
                        ],
                        borderWidth: 1
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
@endpush