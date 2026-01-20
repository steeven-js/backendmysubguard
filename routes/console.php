<?php

use App\Jobs\CheckUserMilestones;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Story 7.9: Check user milestones hourly
Schedule::job(new CheckUserMilestones)->hourly();

// Manual command to check milestones
Artisan::command('milestones:check', function () {
    dispatch(new CheckUserMilestones());
    $this->info('User milestone check dispatched.');
})->purpose('Check and notify user milestones');
