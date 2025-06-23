@extends('karung::layouts.karung_app')

@section('title', 'Manajemen Utang - Modul Toko Karung')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Utang (Pembelian Belum Lunas)</h5>
                     <a href="{{ route('karung.dashboard') }}" class="btn btn-secondary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/></svg>
                        Kembali
                    </a>
                </div>
                <div class="card-body">
                   @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                         <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- ====================================================== --}}
                    {{-- [BARU] TAMBAHKAN BLOK KODE UNTUK MENAMPILKAN ERROR VALIDASI --}}
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
                    {{-- ====================================================== --}}

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kode #</th>
                                    <th>Supplier</th>
                                    <th>Tgl. Transaksi</th>
                                    <th class="text-end">Total Tagihan</th>
                                    <th class="text-end">Sudah Dibayar</th>
                                    <th class="text-end">Sisa Tagihan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payables as $transaction)
                                    <tr>
                                        <td class="font-medium">
                                            <a href="{{ route('karung.purchases.show', $transaction->id) }}" class="text-primary text-decoration-none">
                                                {{ $transaction->purchase_code }}
                                            </a>
                                        </td>
                                        <td>{{ $transaction->supplier->name }}</td>
                                        <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                                        <td class="text-end">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-danger">Rp {{ number_format($transaction->total_amount - $transaction->amount_paid, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('karung.financials.payments.history', ['type' => 'purchase', 'id' => $transaction->id]) }}" class="btn btn-info btn-sm text-white" title="Lihat Riwayat Pembayaran">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.798a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.986 7.986 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zM12 8.5a.5.5 0 0 1 .5-.5h.5a.5.5 0 0 1 0 1h-.5a.5.5 0 0 1-.5-.5m-.002-4.205a7.002 7.002 0 0 0-.299-.985l-.976.219a6.996 6.996 0 0 0 .27.44l.933-.364zM8.5 7.999a.5.5 0 0 1 .5-.5h2.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/><path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m15 0a7 7 0 1 0-14 0 7 7 0 0 0 14 0"/></svg>
                                            </a>
                                            <button type="button" class="btn btn-success btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentModal"
                                                data-transaction-id="{{ $transaction->id }}"
                                                data-transaction-type="purchase"
                                                data-max-amount="{{ $transaction->total_amount - $transaction->amount_paid }}"
                                                data-invoice-number="{{ $transaction->purchase_code }}">
                                                Catat Bayar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada utang yang belum lunas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-3">
                        {{ $payables->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('karung.financials.payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_id" id="modalTransactionId">
                    <input type="hidden" name="transaction_type" id="modalTransactionType">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Catat Pembayaran untuk #<span id="modalInvoiceNumber"></span></h5>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Atur tanggal default ke hari ini
        const today = new Date();
        const year = today.getFullYear();
        const month = ('0' + (today.getMonth() + 1)).slice(-2);
        const day = ('0' + today.getDate()).slice(-2);
        document.getElementById('payment_date').value = `${year}-${month}-${day}`;
        
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Ambil data dari atribut data-* di tombol
            const transactionId = button.getAttribute('data-transaction-id');
            const transactionType = button.getAttribute('data-transaction-type');
            const maxAmount = button.getAttribute('data-max-amount');
            const invoiceNumber = button.getAttribute('data-invoice-number');

            // Masukkan data ke dalam form di modal
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
    });
</script>
@endsection
