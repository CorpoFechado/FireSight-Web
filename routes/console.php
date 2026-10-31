<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily purge for accounts that reached the end of their 30-day grace period (31st day)
Schedule::command('firesight:purge-deleted-accounts')->dailyAt('00:00');
