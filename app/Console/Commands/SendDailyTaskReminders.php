<?php

namespace App\Console\Commands;

use App\Mail\DailyTaskReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-daily-reminders {--user-id= : Send to specific user only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily email reminders to users about their outstanding tasks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting daily task reminder process...');

        // Get users query
        $usersQuery = User::query()->whereNotNull('email');

        // If specific user ID provided, only process that user
        if ($this->option('user-id')) {
            $usersQuery->where('id', $this->option('user-id'));
        }

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            $this->warn('No users found to send reminders to.');

            return self::SUCCESS;
        }

        $emailsSent = 0;
        $usersWithNoTasks = 0;

        foreach ($users as $user) {
            // Get user's outstanding tasks
            $tasks = $this->getUserOutstandingTasks($user);

            if ($tasks->isEmpty()) {
                $usersWithNoTasks++;

                continue;
            }

            // Separate pending and ongoing tasks
            $pendingTasks = $tasks->where('status', 'pending');
            $ongoingTasks = $tasks->where('status', 'ongoing');

            // Calculate total hours
            $totalHours = $tasks->sum('hours');

            // Send email
            try {
                Mail::to($user->email)->send(
                    new DailyTaskReminderMail($user, $pendingTasks, $ongoingTasks, $totalHours)
                );

                $emailsSent++;
                $this->info("✓ Sent reminder to {$user->name} ({$user->email}) - {$tasks->count()} task(s)");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send reminder to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total users processed: {$users->count()}");
        $this->info("Emails sent: {$emailsSent}");
        $this->info("Users with no outstanding tasks: {$usersWithNoTasks}");

        return self::SUCCESS;
    }

    /**
     * Get user's outstanding tasks from the current week
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getUserOutstandingTasks(User $user)
    {
        // Get current week's start and end dates
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        // Get all tasks for this user in the current week
        $tasks = collect();

        // Check if we have a weekday calendar system
        if (class_exists(\App\Models\Weekdaycalendar::class)) {
            $weekdayCalendar = \App\Models\Weekdaycalendar::where('user_id', $user->id)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->first();

            if ($weekdayCalendar) {
                // Get tasks from all days
                $days = [
                    $weekdayCalendar->monday,
                    $weekdayCalendar->tuesday,
                    $weekdayCalendar->wednesday,
                    $weekdayCalendar->thursday,
                    $weekdayCalendar->friday,
                ];

                foreach ($days as $day) {
                    if ($day && isset($day->tasks)) {
                        foreach ($day->tasks as $task) {
                            // Only include pending and ongoing tasks
                            if (in_array($task->status, ['pending', 'ongoing'])) {
                                $tasks->push($task);
                            }
                        }
                    }
                }
            }
        }

        return $tasks;
    }
}
