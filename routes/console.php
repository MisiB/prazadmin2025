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
Schedule::command('tasks:send-daily-reminders')->dailyAt('07:40');
Schedule::command('tasks:create-recurring')->dailyAt('06:00');
Schedule::command('tasks:rollover-weekly')->weeklyOn(1, '06:00'); // Run every Monday at 6:00 AM
Schedule::command('reviews:generate-weekly')->weeklyOn(1, '06:00'); // Generate weekly reviews every Monday at 6:30 AM
Schedule::command('tasks:send-supervisor-reminders')->dailyAt('10:00'); // Send supervisor reminders at 10:00 AM
Schedule::command('tasks:send-supervisor-reminders')->dailyAt('15:00'); // Send supervisor reminders at 3:00 PM
