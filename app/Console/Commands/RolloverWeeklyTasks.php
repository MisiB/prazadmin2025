<?php

namespace App\Console\Commands;

use App\Interfaces\services\itaskReminderService;
use App\Models\User;
use Illuminate\Console\Command;

class RolloverWeeklyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:rollover-weekly {--user-id= : Rollover tasks for specific user only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically rollover pending and ongoing tasks from the previous week to the current week';

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
        $this->info('Starting weekly task rollover process...');

        // Get users query
        $usersQuery = User::query();

        // If specific user ID provided, only process that user
        if ($this->option('user-id')) {
            $usersQuery->where('id', $this->option('user-id'));
        }

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            $this->warn('No users found to process.');

            return self::SUCCESS;
        }

        $totalRolledOver = 0;
        $usersProcessed = 0;
        $usersWithNoTasks = 0;

        foreach ($users as $user) {
            $result = $this->taskReminderService->rolloverpreviousweektasks($user->id);

            if ($result['status'] === 'success') {
                $rolledOverCount = $result['data']['rolled_over_count'] ?? 0;

                if ($rolledOverCount > 0) {
                    $totalRolledOver += $rolledOverCount;
                    $usersProcessed++;
                    $this->info("✓ Rolled over {$rolledOverCount} task(s) for {$user->name} ({$user->email})");
                } else {
                    $usersWithNoTasks++;
                }
            } else {
                $this->error("✗ Failed to rollover tasks for {$user->email}: {$result['message']}");
            }
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total users processed: {$users->count()}");
        $this->info("Users with rolled over tasks: {$usersProcessed}");
        $this->info("Total tasks rolled over: {$totalRolledOver}");
        $this->info("Users with no tasks to rollover: {$usersWithNoTasks}");

        return self::SUCCESS;
    }
}
