<?php

namespace App\Modules\KarungCabang\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\KarungCabang\Models\SalesTransactionDetail; // <-- [TAMBAHKAN] Import model ini

class StoreSalesReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Kita akan tangani otorisasi di controller atau route middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sales_transaction_detail_id' => 'required|integer|exists:karung_sales_transaction_details,id',
            'items.*.product_id' => 'required|integer|exists:karung_products,id',
            
            // [MODIFIKASI] Tambahkan validasi kustom untuk jumlah retur
            'items.*.return_quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Dapatkan ID detail transaksi dari nama atribut, contoh: "items.123.return_quantity"
                    $parts = explode('.', $attribute);
                    $detailId = $this->input('items.' . $parts[1] . '.sales_transaction_detail_id');

                    // Cari detail transaksi pembelian asli di database
                    $originalDetail = SalesTransactionDetail::find($detailId);

                    if (!$originalDetail) {
                        // Ini seharusnya tidak terjadi jika validasi 'exists' di atas bekerja, tapi sebagai pengaman
                        $fail("Detail transaksi asli untuk item ini tidak ditemukan.");
                        return;
                    }

                    // Validasi utama: jumlah retur tidak boleh lebih besar dari jumlah beli
                    if ($value > $originalDetail->quantity) {
                        $fail("Jumlah retur untuk produk '{$originalDetail->product->name}' tidak boleh melebihi {$originalDetail->quantity}.");
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