<?php

namespace App\Modules\Karung\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Karung\Models\PurchaseTransactionDetail;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_transaction_detail_id' => 'required|integer|exists:karung_purchase_transaction_details,id',
            'items.*.product_id' => 'required|integer|exists:karung_products,id',
            
            // [MODIFIKASI FINAL] Sederhanakan validasi untuk menghindari fatal error
            'items.*.return_quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $parts = explode('.', $attribute);
                    $detailId = $this->input('items.' . $parts[1] . '.purchase_transaction_detail_id');
                    $originalDetail = PurchaseTransactionDetail::find($detailId);
                    
                    if (!$originalDetail) {
                        $fail("Detail transaksi asli untuk item ini tidak ditemukan.");
                        return;
                    }

                    // Hanya validasi kuantitas, jangan sentuh relasi ->product
                    if ($value > $originalDetail->quantity) {
                        $fail("Jumlah retur untuk item ini tidak boleh melebihi jumlah yang dibeli ({$originalDetail->quantity}).");
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal harus ada satu barang yang diretur.',
            'items.*.return_quantity.min' => 'Jumlah retur untuk setiap barang minimal harus 1.',
        ];
    }
}