<?php

namespace App\Console\Commands;

use App\Interfaces\services\itaskReminderService;
use App\Mail\DailyTaskReminderMail;
use App\Models\User;
use Carbon\Carbon;
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
    protected $description = 'Send daily email reminders to users about their outstanding tasks from previous days';

    protected $taskReminderService;

    public function __construct(itaskReminderService $taskReminderService)
    {
        parent::__construct();
        $this->taskReminderService = $taskReminderService;
    }

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
            // Get user's outstanding tasks from previous days
            $tasks = $this->taskReminderService->getpreviousdaystasks($user->id);

            if ($tasks->isEmpty()) {
                $usersWithNoTasks++;

                continue;
            }

            // Format tasks for email (convert Task model to format expected by email)
            $formattedTasks = $this->formatTasksForEmail($tasks);

            // Separate pending and ongoing tasks
            $pendingTasks = $formattedTasks->where('status', 'pending');
            $ongoingTasks = $formattedTasks->where('status', 'ongoing');

            // Calculate total hours
            $totalHours = $formattedTasks->sum('hours');

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
     * Format tasks for email template
     * Converts Task model to format expected by DailyTaskReminderMail
     *
     * @param  \Illuminate\Support\Collection  $tasks
     * @return \Illuminate\Support\Collection
     */
    protected function formatTasksForEmail($tasks)
    {
        return $tasks->map(function ($task) {
            $dayName = $task->calendarday ? Carbon::parse($task->calendarday->maindate)->format('l') : 'Unknown';

            return (object) [
                'name' => $task->title,
                'hours' => $task->duration ?? 0,
                'day' => $dayName,
                'status' => $task->status,
                'comment' => $task->approval_comment ?? null,
            ];
        });
    }
}
