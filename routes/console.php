<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('finance:sync', function () {
    $this->info('Synchronizing financial records with approved payments and expenses...');
    \App\Services\FinancialRecordSyncService::syncAll();
    $count = \App\Models\FinancialRecord::count();
    $this->info("Done! Total financial records in database: {$count}");
})->purpose('Synchronize all approved payments and expenses into financial_records');

// Auto-expire posts whose expires_at has passed — runs every hour
Schedule::command('posts:expire')->hourly();
