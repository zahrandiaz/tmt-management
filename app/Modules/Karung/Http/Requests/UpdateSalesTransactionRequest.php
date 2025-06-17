<?php

namespace App\Modules\Karung\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // [BARU]

class UpdateSalesTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_date'      => ['required', 'date'],
            'customer_id'           => ['nullable', 'integer', 'exists:karung_customers,id'],
            'notes'                 => ['nullable', 'string'],
            // [BARU] Aturan untuk status pembayaran
            'payment_method'        => ['required', 'string', 'max:50'],
            'payment_status'        => ['required', 'string', Rule::in(['Lunas', 'Belum Lunas'])],
            'amount_paid'           => ['nullable', 'numeric', 'min:0'],
            // Aturan detail tidak berubah
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.selling_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ];
    }
}