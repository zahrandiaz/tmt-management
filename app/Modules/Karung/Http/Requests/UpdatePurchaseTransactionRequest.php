<?php

namespace App\Modules\Karung\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // [FIX] Hitung total amount dari details untuk validasi amount_paid
        $totalAmount = collect($this->input('details'))->sum(function ($detail) {
            return ($detail['quantity'] ?? 0) * ($detail['purchase_price_at_transaction'] ?? 0);
        });
        
        return [
            'transaction_date'      => ['required', 'date'],
            'supplier_id'           => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'purchase_reference_no' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'attachment_path'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.purchase_price_at_transaction' => ['required', 'numeric', 'min:0'],
            
            // [FIX] Menggunakan satu set aturan pembayaran yang benar
            'payment_method' => ['required', 'string', Rule::in(['Tunai', 'Transfer Bank', 'Lainnya'])],
            'payment_status' => ['required', 'string', Rule::in(['Lunas', 'Belum Lunas'])],
            'amount_paid'    => [
                'nullable',
                'numeric',
                'min:0',
                'max:' . $totalAmount, // Validasi agar tidak lebih besar dari total
                Rule::requiredIf(fn () => $this->input('payment_status') === 'Belum Lunas'),
            ],
        ];
    }
}