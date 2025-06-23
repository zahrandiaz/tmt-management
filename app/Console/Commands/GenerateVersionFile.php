<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateVersionFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:version'; // Nama perintah kita

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a version.json file with current Git information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating version.json file...');

        try {
            $version = trim(shell_exec('git describe --tags --abbrev=0'));
            $commitHash = trim(shell_exec('git rev-parse --short HEAD'));
            $commitDate = new \DateTime(trim(shell_exec('git log -n1 --pretty=%ci HEAD')));
            $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));

            $versionData = [
                'version' => $version ?: 'dev', // Jika tidak ada tag, anggap 'dev'
                'commit' => $commitHash,
                'commit_date' => $commitDate->format('Y-m-d H:i:s'),
                'branch' => $branch,
            ];

            $filePath = base_path('version.json');
            File::put($filePath, json_encode($versionData, JSON_PRETTY_PRINT));
            
            $this->info('version.json file generated successfully at ' . $filePath);
            
        } catch (\Exception $e) {
            $this->error('Failed to generate version.json file.');
            $this->error($e->getMessage());
            return 1; // Return non-zero status code on failure
        }
        
        return 0; // Success
    }
}