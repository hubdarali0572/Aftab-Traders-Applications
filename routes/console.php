<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// database backup command
Schedule::command('app:database-backup')->daily();

// Schedule your cache clear command to run hourly
Schedule::command('app:cache-clear')->hourly();
