<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\itaskinstanceInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Models\User;
use App\Models\WeeklyTaskReview;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateWeeklyTaskReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:generate-weekly {--week-start= : Specific week start date (Y-m-d). Defaults to last week}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate weekly task reviews for all users based on their actual tasks';

    protected $taskRepository;

    protected $taskinstanceRepository;

    public function __construct(
        itaskInterface $taskRepository,
        itaskinstanceInterface $taskinstanceRepository
    ) {
        parent::__construct();
        $this->taskRepository = $taskRepository;
        $this->taskinstanceRepository = $taskinstanceRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating weekly task reviews...');

        // Determine which week to process
        if ($this->option('week-start')) {
            $weekStart = Carbon::parse($this->option('week-start'))->startOfWeek();
        } else {
            $weekStart = Carbon::now()->subWeek()->startOfWeek();
        }
        $weekEnd = $weekStart->copy()->endOfWeek();

        $this->info("Processing week: {$weekStart->format('Y-m-d')} to {$weekEnd->format('Y-m-d')}");

        $users = User::all();
        $reviewsGenerated = 0;
        $reviewsUpdated = 0;
        $usersSkipped = 0;

        foreach ($users as $user) {
            // Get user's tasks for the week (based on calendarday maindate)
            $tasks = $this->taskRepository->gettasksbyuseridanddaterange(
                $user->id,
                $weekStart->format('Y-m-d'),
                $weekEnd->format('Y-m-d')
            );

            if ($tasks->isEmpty()) {
                $usersSkipped++;

                continue;
            }

            // Calculate metrics
            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 'completed')->count();
            $incompleteTasks = $totalTasks - $completedTasks;
            $completionRate = $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100, 2)
                : 0;

            // Get task IDs for this user's tasks
            $taskIds = $tasks->pluck('id')->toArray();

            // Get task instances for hours calculation
            $taskInstances = $this->taskinstanceRepository->getinstancesbytaskids($taskIds)
                ->filter(function ($instance) use ($weekStart, $weekEnd) {
                    return $instance->date >= $weekStart->format('Y-m-d')
                        && $instance->date <= $weekEnd->format('Y-m-d');
                });

            $totalHoursPlanned = round((float) $taskInstances->sum('planned_hours'), 2);
            $totalHoursCompleted = round((float) $taskInstances->sum('worked_hours'), 2);

            // Build task reviews array
            $taskReviews = $tasks->map(function ($task) use ($weekStart) {
                $dayName = $task->calendarday
                    ? Carbon::parse($task->calendarday->maindate)->format('l')
                    : 'Unknown';

                // Get hours from task instances or use duration if available
                $taskInstanceHours = $task->taskinstances
                    ->where('date', '>=', $weekStart->format('Y-m-d'))
                    ->sum('planned_hours');
                $hours = $taskInstanceHours > 0 ? $taskInstanceHours : ($task->duration ?? 0);

                return [
                    'task_name' => $task->title,
                    'hours' => round((float) $hours, 2),
                    'day' => $dayName,
                    'original_status' => $task->status,
                    'was_completed' => $task->status === 'completed',
                    'completion_comment' => '',
                ];
            })->toArray();

            // Check if review already exists
            $existingReview = WeeklyTaskReview::where('user_id', $user->id)
                ->where('week_start_date', $weekStart->format('Y-m-d'))
                ->first();

            $reviewData = [
                'user_id' => $user->id,
                'week_start_date' => $weekStart->format('Y-m-d'),
                'week_end_date' => $weekEnd->format('Y-m-d'),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'incomplete_tasks' => $incompleteTasks,
                'completion_rate' => $completionRate,
                'total_hours_planned' => $totalHoursPlanned,
                'total_hours_completed' => $totalHoursCompleted,
                'task_reviews' => $taskReviews,
                'reviewed_at' => now(),
                'is_submitted' => true, // Auto-submitted
            ];

            if ($existingReview) {
                $existingReview->update($reviewData);
                $reviewsUpdated++;
                $this->line("  ✓ Updated review for {$user->name}");
            } else {
                WeeklyTaskReview::create($reviewData);
                $reviewsGenerated++;
                $this->line("  ✓ Generated review for {$user->name}");
            }
        }

        $this->info('');
        $this->info('=== Summary ===');
        $this->info("Reviews generated: {$reviewsGenerated}");
        $this->info("Reviews updated: {$reviewsUpdated}");
        $this->info("Users skipped (no tasks): {$usersSkipped}");
        $this->info("Total users processed: {$users->count()}");

        return self::SUCCESS;
    }
}
