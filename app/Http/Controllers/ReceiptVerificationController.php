<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Karung\Models\SalesTransaction;

class ReceiptVerificationController extends Controller
{
    /**
     * Menampilkan halaman verifikasi struk publik berdasarkan UUID.
     *
     * @param string $uuid
     * @return \Illuminate\View\View
     */
    public function verify($uuid)
    {
        // Cari transaksi berdasarkan UUID. Jika tidak ada, tampilkan halaman 404.
        $transaction = SalesTransaction::where('uuid', $uuid)
            ->with(['details.product', 'customer', 'user']) // Eager load relasi yang dibutuhkan
            ->firstOrFail();

        // Kirim data transaksi ke view publik
        return view('public.receipt_verification', compact('transaction'));
    }
}