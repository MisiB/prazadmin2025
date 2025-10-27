<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily scheduled tasks
Schedule::command('app:userstatementcreation')->daily();
Schedule::command('app:statementsrollover')->monthly();
Schedule::command('app:updateactinghod')->daily();
Schedule::command('tasks:send-daily-reminders')->dailyAt('08:00');
