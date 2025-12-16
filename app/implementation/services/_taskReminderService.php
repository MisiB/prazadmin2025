<?php

namespace App\implementation\services;

use App\Interfaces\repositories\icalendarInterface;
use App\Interfaces\repositories\itaskinstanceInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\itaskinstanceService;
use App\Interfaces\services\itaskReminderService;
use Carbon\Carbon;

class _taskReminderService implements itaskReminderService
{
    protected $taskRepository;

    protected $taskinstanceService;

    protected $taskinstanceRepository;

    protected $calendarRepository;

    public function __construct(
        itaskInterface $taskRepository,
        itaskinstanceService $taskinstanceService,
        itaskinstanceInterface $taskinstanceRepository,
        icalendarInterface $calendarRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->taskinstanceService = $taskinstanceService;
        $this->taskinstanceRepository = $taskinstanceRepository;
        $this->calendarRepository = $calendarRepository;
    }

    /**
     * Get outstanding tasks from previous days for a user
     * Returns tasks from all days before today that are pending or ongoing
     */
    public function getpreviousdaystasks($userId)
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();
        $startDate = $today->copy()->subYear(); // Go back a year to get all previous days

        return $this->taskRepository->getpendingorongoingtasksbyuseranddaterange(
            $userId,
            $startDate->format('Y-m-d'),
            $yesterday->format('Y-m-d')
        );
    }

    /**
     * Get pending or ongoing tasks from previous week for a user
     */
    public function getpreviousweektasks($userId)
    {
        $previousWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $previousWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        return $this->taskRepository->getpendingorongoingtasksbyuseranddaterange(
            $userId,
            $previousWeekStart->format('Y-m-d'),
            $previousWeekEnd->format('Y-m-d')
        );
    }

    /**
     * Rollover tasks from previous week to current week
     * Moves tasks to the first day (Monday) of the current week
     */
    public function rolloverpreviousweektasks($userId)
    {
        try {
            $tasks = $this->getpreviousweektasks($userId);

            if ($tasks->isEmpty()) {
                return [
                    'status' => 'success',
                    'message' => 'No tasks to rollover',
                    'data' => ['rolled_over_count' => 0],
                ];
            }

            // Get current week's first day (Monday)
            $currentWeekStart = Carbon::now()->startOfWeek();
            $currentWeekStartDate = $currentWeekStart->format('Y-m-d');

            // Find the calendar day for the current week's Monday
            $currentWeekMonday = $this->calendarRepository->getcalendardaybydate($currentWeekStartDate);

            if (! $currentWeekMonday) {
                return [
                    'status' => 'error',
                    'message' => 'Calendar day not found for current week start ('.$currentWeekStartDate.'). Please ensure the calendar is initialized.',
                ];
            }

            $rolledOverCount = 0;
            $errors = [];

            foreach ($tasks as $task) {
                // Get the active task instance for this task
                $activeInstance = $this->taskinstanceRepository->getactiveinstancebytaskid($task->id);

                if ($activeInstance) {
                    // Use the existing rolloverinstance method
                    $result = $this->taskinstanceService->rolloverinstance($activeInstance->id, $currentWeekStartDate);

                    if ($result['status'] === 'success') {
                        $rolledOverCount++;
                    } else {
                        $errors[] = "Task '{$task->title}' (ID: {$task->id}): {$result['message']}";
                    }
                } else {
                    // No active instance, just move the task to the new week
                    $updateResult = $this->taskRepository->updatetask($task->id, [
                        'calendarday_id' => $currentWeekMonday->id,
                        'user_id' => $task->user_id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'priority' => $task->priority,
                        'duration' => $task->duration,
                        'uom' => $task->uom,
                        'individualworkplan_id' => $task->individualworkplan_id,
                    ]);

                    if ($updateResult['status'] === 'success') {
                        // Create a new instance for the current week
                        $instanceResult = $this->taskinstanceService->createinstance([
                            'task_id' => $task->id,
                            'date' => $currentWeekStartDate,
                            'planned_hours' => $task->duration ?? 0,
                            'worked_hours' => 0,
                            'status' => $task->status === 'ongoing' ? 'ongoing' : 'pending',
                        ]);

                        if ($instanceResult['status'] === 'success') {
                            $rolledOverCount++;
                        } else {
                            $errors[] = "Task '{$task->title}' (ID: {$task->id}): Failed to create instance";
                        }
                    } else {
                        $errors[] = "Task '{$task->title}' (ID: {$task->id}): Failed to update task";
                    }
                }
            }

            $message = "Rolled over {$rolledOverCount} task(s) from previous week";
            if (! empty($errors)) {
                $message .= '. Errors: '.implode('; ', $errors);
            }

            return [
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'rolled_over_count' => $rolledOverCount,
                    'total_tasks' => $tasks->count(),
                    'errors' => $errors,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
