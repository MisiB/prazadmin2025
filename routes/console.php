<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily scheduled tasks
Schedule::command('leavestatement:newuserstatementcreation'); // Manually run on account creation
Schedule::command('leavestatement:updateactinghod')->dailyAt('08:00');
Schedule::command('leavestatement:accumulate')->monthly();
Schedule::command('leavestatement:rollover')->yearly();
Schedule::command('tasks:send-daily-reminders')->dailyAt('08:00');
Schedule::command('tasks:create-recurring')->dailyAt('06:00');
