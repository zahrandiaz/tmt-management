{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Manajemen Piutang (Penjualan Belum Lunas)
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
                            <h5 class="mb-0">Manajemen Piutang (Penjualan Belum Lunas)</h5>
                            <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                            </a>
                        </div>
                        <div class="card-body">
                            @include('karung::components.flash-message')

                            @if ($errors->any())
                                <div class="alert alert-danger mb-4">
                                    <strong>Whoops! Ada beberapa masalah dengan input Anda.</strong>
                                    <ul class="mt-2 mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Pelanggan</th>
                                            <th>Tgl. Transaksi</th>
                                            <th class="text-end">Total Tagihan</th>
                                            <th class="text-end">Sudah Dibayar</th>
                                            <th class="text-end">Sisa Tagihan</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($receivables as $transaction)
                                            <tr>
                                                <td class="font-medium">
                                                    <a href="{{ route('karung.sales.show', $transaction->id) }}" class="text-primary text-decoration-none">
                                                        {{ $transaction->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                                                <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                                                <td class="text-end">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-danger">Rp {{ number_format($transaction->total_amount - $transaction->amount_paid, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('karung.financials.payments.history', ['type' => 'sales', 'id' => $transaction->id]) }}" class="btn btn-info btn-sm text-white" title="Lihat Riwayat Pembayaran">
                                                        <i class="bi bi-clock-history"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-success btn-sm" title="Catat Pembayaran"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#paymentModal"
                                                            data-transaction-id="{{ $transaction->id }}"
                                                            data-transaction-type="sales"
                                                            data-max-amount="{{ $transaction->total_amount - $transaction->amount_paid }}"
                                                            data-invoice-number="{{ $transaction->invoice_number }}">
                                                        <i class="bi bi-cash-coin"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada piutang yang belum lunas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $receivables->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Pembayaran --}}
            <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('karung.financials.payments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="transaction_id" id="modalTransactionId">
                            <input type="hidden" name="transaction_type" id="modalTransactionType">
                            <div class="modal-header">
                                <h5 class="modal-title" id="paymentModalLabel">Catat Pembayaran untuk Invoice #<span id="modalInvoiceNumber"></span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Tanggal Pembayaran</label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Jumlah Pembayaran</label>
                                    <input type="number" name="amount" id="modalAmount" step="1" class="form-control" placeholder="0" required>
                                    <div class="form-text">Sisa tagihan: <span id="modalMaxAmountText"></span></div>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Metode Pembayaran (Opsional)</label>
                                    <input type="text" name="payment_method" id="payment_method" class="form-control" placeholder="Contoh: Tunai, Transfer BCA">
                                </div>
                                <div>
                                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                                    <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const paymentDateInput = document.getElementById('payment_date');
                if(paymentDateInput) {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = ('0' + (today.getMonth() + 1)).slice(-2);
                    const day = ('0' + today.getDate()).slice(-2);
                    paymentDateInput.value = `${year}-${month}-${day}`;
                }

                const paymentModal = document.getElementById('paymentModal');
                if(paymentModal) {
                    paymentModal.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        
                        const transactionId = button.getAttribute('data-transaction-id');
                        const transactionType = button.getAttribute('data-transaction-type');
                        const maxAmount = button.getAttribute('data-max-amount');
                        const invoiceNumber = button.getAttribute('data-invoice-number');

                        const modalTransactionIdInput = paymentModal.querySelector('#modalTransactionId');
                        const modalTransactionTypeInput = paymentModal.querySelector('#modalTransactionType');
                        const modalAmountInput = paymentModal.querySelector('#modalAmount');
                        const modalMaxAmountText = paymentModal.querySelector('#modalMaxAmountText');
                        const modalInvoiceNumber = paymentModal.querySelector('#modalInvoiceNumber');

                        modalTransactionIdInput.value = transactionId;
                        modalTransactionTypeInput.value = transactionType;
                        modalAmountInput.value = maxAmount;
                        modalAmountInput.max = maxAmount;
                        modalMaxAmountText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(maxAmount);
                        modalInvoiceNumber.textContent = invoiceNumber;
                    });
                }
            });
        </script>
    </x-slot>
</x-app-layout>