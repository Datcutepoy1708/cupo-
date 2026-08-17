<?php

use App\Jobs\ActivateFlashSalesJob;
use App\Jobs\DeactivateExpiredFlashSalesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ActivateFlashSalesJob)->everyMinute();
Schedule::job(new DeactivateExpiredFlashSalesJob)->everyMinute();
