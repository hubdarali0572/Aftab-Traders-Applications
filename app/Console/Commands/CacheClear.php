<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CacheClear extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:cache-clear';

    /**
     * The console command description.
     */
    protected $description = 'Clear all framework and application caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('Starting full system cache clear...');

        // 1. Clear All Framework Optimizations
        Artisan::call('optimize:clear');
        $this->info('✓ All framework optimization caches cleared (Routes, Config, Views, Events).');

        // 2. Clear Application Cache
        Artisan::call('cache:clear');
        $this->info('✓ Application data cache cleared.');

        // 3. Clear Compiled Services and Packages
        Artisan::call('clear-compiled');
        $this->info('✓ Compiled service and package files removed.');

        // 4. Clear Expired Password Reset Tokens
        Artisan::call('auth:clear-resets');
        $this->info('✓ Expired password reset tokens cleared.');

        $this->newLine();
        // CHANGED: $this->success to $this->info
        $this->info('System Refresh Completed Successfully.');

        return Command::SUCCESS;
    }
}