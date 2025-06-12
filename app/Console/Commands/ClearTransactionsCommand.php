<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // <-- Import DB Facade

class ClearTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Ini adalah nama perintah yang akan kita ketik di terminal
    protected $signature = 'app:clear-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    // Ini adalah deskripsi yang muncul saat Anda menjalankan 'php artisan list'
    protected $description = 'Menghapus semua data transaksi (pembelian & penjualan) dari modul Toko Karung';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Meminta konfirmasi dari pengguna sebelum melanjutkan
        if ($this->confirm('Apakah Anda yakin ingin menghapus SEMUA data transaksi pembelian dan penjualan? Aksi ini tidak dapat diurungkan.')) {
            
            $this->info('Memulai proses pembersihan data transaksi...');

            // Nonaktifkan pengecekan foreign key untuk sementara agar bisa menghapus
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Daftar tabel transaksi yang akan dikosongkan
            $transactionTables = [
                'karung_purchase_transaction_details',
                'karung_purchase_transactions',
                'karung_sales_transaction_details',
                'karung_sales_transactions',
            ];

            foreach ($transactionTables as $table) {
                // Mengosongkan tabel
                DB::table($table)->truncate();
                $this->line("Tabel '{$table}' berhasil dikosongkan.");
            }

            // Aktifkan kembali pengecekan foreign key
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('Semua data transaksi telah berhasil dihapus!');
            return 0; // Mengindikasikan perintah sukses
        }

        $this->comment('Proses dibatalkan.');
        return 1; // Mengindikasikan perintah dibatalkan
    }
}
