{{-- File: app/Modules/Karung/Resources/views/components/payment-modal.blade.php --}}
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
                        <label for="payment_date" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah Pembayaran <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="modalAmount" step="1" class="form-control" placeholder="0" required>
                        <div class="form-text">Sisa tagihan: <span id="modalMaxAmountText" class="fw-bold"></span></div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran</label>
                        <input type="text" name="payment_method" id="payment_method" class="form-control" placeholder="Contoh: Tunai, Transfer BCA">
                    </div>
                    <div>
                        <label for="notes" class="form-label">Catatan</label>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            // Atur tanggal default ke hari ini setiap kali modal dibuka
            const paymentDateInput = paymentModal.querySelector('#payment_date');
            const today = new Date();
            const year = today.getFullYear();
            const month = ('0' + (today.getMonth() + 1)).slice(-2);
            const day = ('0' + today.getDate()).slice(-2);
            paymentDateInput.value = `${year}-${month}-${day}`;

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
                modalAmountInput.max = maxAmount; // Set validasi maksimum
                modalMaxAmountText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(maxAmount);
                modalInvoiceNumber.textContent = invoiceNumber;
            });
        }
    });
</script>
@endpush