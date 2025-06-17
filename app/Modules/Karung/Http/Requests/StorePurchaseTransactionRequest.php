<?php

namespace App\Modules\Karung\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // [BARU]

class StorePurchaseTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_date'      => ['required', 'date'],
            'supplier_id'           => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'purchase_reference_no' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'attachment_path'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            // [BARU] Aturan untuk status pembayaran
            'payment_method'        => ['required', 'string', 'max:50'],
            'payment_status'        => ['required', 'string', Rule::in(['Lunas', 'Belum Lunas'])],
            'amount_paid'           => ['nullable', 'numeric', 'min:0'],
            // Aturan detail tidak berubah
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.purchase_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ];
    }
}