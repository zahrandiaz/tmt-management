<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportExportedNotification extends Notification
{
    use Queueable;

    protected $fileName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Kita akan simpan notifikasi ini di database
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Laporan penjualan Anda sudah siap untuk diunduh.',
            'file_name' => $this->fileName,
            'download_url' => route('karung.reports.download', ['filename' => $this->fileName]),
        ];
    }
}