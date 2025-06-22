<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExportedReport; // <-- Import model kita
use Illuminate\Support\Facades\Storage; // <-- Import Storage facade
use Carbon\Carbon; // <-- Import Carbon untuk manipulasi tanggal

class PruneExportedReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // [MODIFIKASI] Signature command yang lebih standar
    protected $signature = 'reports:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    // [MODIFIKASI] Deskripsi yang lebih jelas
    protected $description = 'Hapus file dan data laporan yang sudah diekspor lebih dari 30 hari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses pembersihan laporan lama...');

        // Tentukan tanggal batas (30 hari yang lalu dari sekarang)
        $cutoffDate = Carbon::now()->subDays(7);

        // Cari semua laporan yang lebih tua dari tanggal batas
        $oldReports = ExportedReport::where('created_at', '<=', $cutoffDate)->get();

        if ($oldReports->isEmpty()) {
            $this->info('Tidak ada laporan lama untuk dibersihkan. Selesai.');
            return 0; // Keluar dengan status sukses
        }

        $count = $oldReports->count();
        $this->info("Ditemukan {$count} laporan lama yang akan dihapus.");

        foreach ($oldReports as $report) {
            // Hapus file fisik dari storage
            if (Storage::disk($report->disk)->exists($report->path)) {
                Storage::disk($report->disk)->delete($report->path);
                $this->line("File '{$report->filename}' telah dihapus dari storage.");
            } else {
                $this->warn("File '{$report->filename}' tidak ditemukan di storage, hanya menghapus catatan database.");
            }

            // Hapus catatan dari database
            $report->delete();
        }

        $this->info("Proses pembersihan selesai. Total {$count} laporan lama telah dihapus.");
        return 0; // Keluar dengan status sukses
    }
}