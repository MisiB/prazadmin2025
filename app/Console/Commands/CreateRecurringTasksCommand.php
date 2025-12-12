<?php

namespace App\Console\Commands;

use App\Interfaces\services\irecurringTaskService;
use Illuminate\Console\Command;

class CreateRecurringTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:create-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tasks from active recurring task schedules';

    /**
     * Execute the console command.
     */
    public function handle(irecurringTaskService $recurringTaskService)
    {
        $this->info('Processing recurring tasks...');

        $result = $recurringTaskService->processrecurringtasks();

        if ($result['status'] === 'success') {
            $this->info("✓ {$result['message']}");

            if (! empty($result['errors'])) {
                $this->warn('Errors encountered:');
                foreach ($result['errors'] as $error) {
                    $this->error("  - {$error}");
                }
            }
        } else {
            $this->error('Failed to process recurring tasks');
        }
    }
}
