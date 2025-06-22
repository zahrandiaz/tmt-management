<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\ReportExportedNotification;
use App\Models\ExportedReport; // <-- [MODIFIKASI] Import model baru kita

class ExportSalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $startDate;
    protected $endDate;
    protected $customerId;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $startDate, $endDate, $customerId, $userId)
    {
        $this->user = $user;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->customerId = $customerId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Buat nama file yang unik
        $fileName = 'Laporan_Penjualan_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = 'public/report_exports/' . $fileName;

        // 2. Buat objek export
        $export = new SalesReportExport(
            $this->startDate,
            $this->endDate,
            $this->customerId,
            $this->userId
        );
        
        // 3. Simpan file ke storage
        Excel::store($export, $filePath);

        // 4. [BARU] Simpan catatan ke database
        ExportedReport::create([
            'user_id'  => $this->user->id,
            'filename' => $fileName,
            'path'     => $filePath,
            'disk'     => 'public', // Menggunakan disk 'public'
        ]);

        // 5. Kirim notifikasi ke pengguna (tetap ada)
        $this->user->notify(new ReportExportedNotification($fileName));
    }
}