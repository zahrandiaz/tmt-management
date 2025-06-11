<?php

namespace App\Http\Controllers\Tmt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Karung\Models\Setting; // Import model Setting dari modul Karung

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index()
    {
        // TODO: Nanti ini harus dinamis berdasarkan Instansi Bisnis
        $currentBusinessUnitId = 1;

        // Ambil semua pengaturan untuk business unit ini dan ubah menjadi format yang mudah diakses
        // ->pluck('setting_value', 'setting_key') akan membuat array asosiatif
        // contoh: ['automatic_stock_management' => 'true', 'default_tax_rate' => '11']
        $settings = Setting::where('business_unit_id', $currentBusinessUnitId)
                           ->pluck('setting_value', 'setting_key');

        return view('tmt.settings.index', compact('settings'));
    }

    /**
     * Menyimpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        // TODO: Nanti ini harus dinamis berdasarkan Instansi Bisnis
        $currentBusinessUnitId = 1;

        // Validasi input jika perlu. Untuk checkbox, kita bisa langsung proses.
        $validatedData = $request->validate([
            'automatic_stock_management' => ['sometimes', 'string'], // Diterima sebagai 'true' atau 'false'
        ]);

        // Looping melalui setiap pengaturan yang dikirim dari form
        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(
                [
                    'business_unit_id' => $currentBusinessUnitId,
                    'setting_key'      => $key,
                ],
                [
                    'setting_value'    => $value,
                ]
            );
        }

        // Handle checkbox yang tidak dikirim saat tidak dicentang
        if (!$request->has('automatic_stock_management')) {
            Setting::updateOrCreate(
                [
                    'business_unit_id' => $currentBusinessUnitId,
                    'setting_key'      => 'automatic_stock_management',
                ],
                [
                    'setting_value'    => 'false',
                ]
            );
        }

        return redirect()->route('tmt.admin.settings.index')
                         ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
