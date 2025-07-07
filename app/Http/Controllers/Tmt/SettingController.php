<?php

namespace App\Http\Controllers\Tmt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $currentBusinessUnitId = 1; // TODO: Dinamis

        $settings = Setting::where('business_unit_id', $currentBusinessUnitId)
                            ->pluck('setting_value', 'setting_key');

        return view('tmt.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $currentBusinessUnitId = 1; // TODO: Dinamis

        // [MODIFIKASI] Tambahkan validasi untuk informasi toko
        $validatedData = $request->validate([
            'store_name'    => ['nullable', 'string', 'max:255'],
            'store_address' => ['nullable', 'string'],
            'store_phone'   => ['nullable', 'string', 'max:50'],
            'automatic_stock_management' => ['sometimes', 'string'],
        ]);

        // [REFACTOR] Logika penyimpanan yang lebih scalable
        $settingsToUpdate = [
            'store_name'    => $request->input('store_name', ''),
            'store_address' => $request->input('store_address', ''),
            'store_phone'   => $request->input('store_phone', ''),
            'automatic_stock_management' => $request->has('automatic_stock_management') ? 'true' : 'false',
        ];

        foreach ($settingsToUpdate as $key => $value) {
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

        return redirect()->route('tmt.admin.settings.index')
                         ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}