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
use App\Models\User; // <-- Import User model
use App\Notifications\ReportExportedNotification; // <-- Import Notifikasi kita

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
     * [MODIFIKASI] Menerima objek User
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
        $fileName = 'Laporan_Penjualan_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        $export = new SalesReportExport(
            $this->startDate,
            $this->endDate,
            $this->customerId,
            $this->userId
        );
        
        Excel::store($export, 'public/report_exports/' . $fileName);

        // [MODIFIKASI] Kirim notifikasi ke pengguna yang meminta laporan
        $this->user->notify(new ReportExportedNotification($fileName));
    }
}