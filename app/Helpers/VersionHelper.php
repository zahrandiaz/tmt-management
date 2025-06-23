<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // [OPSIONAL] Untuk logging jika ada error

class VersionHelper
{
    /**
     * @var \stdClass|null
     * Menyimpan data versi yang sudah di-decode untuk menghindari pembacaan file berulang kali.
     */
    protected static $versionData = null;

    /**
     * Mengambil informasi versi dari file version.json.
     *
     * @param string|null $key Kunci spesifik yang ingin diambil (e.g., 'version', 'commit').
     * @return \stdClass|string|null Mengembalikan seluruh objek data, nilai spesifik, atau null.
     */
    public static function get(string $key = null)
    {
        // Cek apakah data sudah pernah dibaca sebelumnya dalam request ini
        if (self::$versionData === null) {
            $path = base_path('version.json');

            if (!File::exists($path)) {
                // Set data menjadi array kosong jika file tidak ada, agar tidak coba baca lagi
                self::$versionData = (object)[]; 
            } else {
                self::$versionData = json_decode(File::get($path));

                // Jika json_decode gagal, catat error dan set data ke array kosong
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Gagal membaca file version.json: ' . json_last_error_msg());
                    self::$versionData = (object)[];
                }
            }
        }

        // Jika key spesifik diminta, kembalikan nilainya.
        if ($key) {
            return self::$versionData->{$key} ?? null;
        }

        // Jika tidak ada data sama sekali (file tidak ada atau error), kembalikan null
        if (empty((array)self::$versionData)) {
            return null;
        }

        // Kembalikan seluruh objek data
        return self::$versionData;
    }
}