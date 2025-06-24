<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Karung\Models\SalesTransaction;

class ReceiptVerificationController extends Controller
{
    /**
     * [BARU] Menampilkan halaman form untuk input kode verifikasi manual.
     */
    public function showVerificationForm()
    {
        return view('public.verification_form');
    }

    /**
     * [BARU] Memproses kode verifikasi yang di-submit dari form.
     */
    public function verifyByCode(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'verification_code' => 'required|string|size:8',
        ], [
            'verification_code.required' => 'Kode verifikasi wajib diisi.',
            'verification_code.size' => 'Kode verifikasi harus terdiri dari 8 karakter.',
        ]);

        // 2. Cari transaksi berdasarkan kode
        $verificationCode = strtoupper($request->input('verification_code'));
        $transaction = SalesTransaction::where('verification_code', $verificationCode)->first();

        // 3. Jika tidak ditemukan, kembali dengan pesan error
        if (!$transaction) {
            return redirect()->route('receipt.form')
                ->with('error', 'Kode verifikasi tidak ditemukan atau tidak valid.')
                ->withInput();
        }

        // 4. Jika ditemukan, arahkan ke halaman verifikasi via UUID
        return redirect()->route('receipt.verify', ['uuid' => $transaction->uuid]);
    }

    /**
     * Menampilkan halaman verifikasi struk publik berdasarkan UUID.
     * (Method ini tidak berubah)
     */
    public function verify($uuid)
    {
        $transaction = SalesTransaction::where('uuid', $uuid)
            ->with(['details.product', 'customer', 'user'])
            ->firstOrFail();

        return view('public.receipt_verification', compact('transaction'));
    }
}