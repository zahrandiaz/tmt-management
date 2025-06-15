<?php

namespace App\Modules\Karung\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesTransactionRequest extends FormRequest
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
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.selling_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ];
    }
}