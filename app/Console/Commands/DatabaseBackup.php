<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupPath = storage_path('app/backup');

        // Make sure backup folder exists
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = "backup-" . now()->format('d-m-y') . ".gz";

        // Delete any previous backup in the folder
        $existingBackups = glob($backupPath . '/backup-*.gz');
        foreach ($existingBackups as $file) {
            unlink($file);
        }

        // Only include --password if a password exists
        $passwordOption = env('DB_PASSWORD') ? "--password=" . env('DB_PASSWORD') : "";

        // Build the mysqldump command
        $command = "mysqldump --user=" . env('DB_USERNAME') . " $passwordOption --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . " | gzip > \"$backupPath/$filename\"";

        $returnVar = null;
        $output  = null;

        // Execute the command
        exec($command, $output, $returnVar);

        $this->info("Database backup created successfully: $filename");
    }
}
