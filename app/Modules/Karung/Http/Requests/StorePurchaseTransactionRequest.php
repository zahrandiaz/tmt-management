<?php

namespace App\Modules\Karung\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePurchaseTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi sudah ditangani oleh Policy di Controller,
        // jadi di sini kita izinkan saja.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Pindahkan semua aturan validasi dari method store() ke sini.
        return [
            'transaction_date'      => ['required', 'date'],
            'supplier_id'           => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'purchase_reference_no' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'attachment_path'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.purchase_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ];
    }
}