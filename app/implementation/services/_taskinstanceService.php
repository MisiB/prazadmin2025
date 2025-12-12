<?php

namespace App\implementation\services;

use App\Interfaces\repositories\itaskinstanceInterface;
use App\Interfaces\services\itaskinstanceService;
use App\Models\Calendarday;
use App\Models\Task;
use Carbon\Carbon;

class _taskinstanceService implements itaskinstanceService
{
    protected $taskinstanceRepository;

    public function __construct(itaskinstanceInterface $taskinstanceRepository)
    {
        $this->taskinstanceRepository = $taskinstanceRepository;
    }

    public function getinstancesbydate($date)
    {
        return $this->taskinstanceRepository->getinstancesbydate($date);
    }

    public function getinstancesbydateanduser($date, $userId)
    {
        return $this->taskinstanceRepository->getinstancesbydateanduser($date, $userId);
    }

    public function createinstance(array $data)
    {
        return $this->taskinstanceRepository->create($data);
    }

    /**
     * Log worked hours for a task instance
     */
    public function loghours($instanceId, $workedHours)
    {
        try {
            $instance = $this->taskinstanceRepository->getbyid($instanceId);

            if (! $instance) {
                return ['status' => 'error', 'message' => 'Task instance not found'];
            }

            // Check if the parent task is marked as ongoing
            $task = Task::find($instance->task_id);
            if (! $task) {
                return ['status' => 'error', 'message' => 'Task not found'];
            }

            if ($task->status !== 'ongoing') {
                return ['status' => 'error', 'message' => 'You can only log hours on tasks that are marked as ongoing'];
            }

            // Update the task instance
            $updateResult = $this->taskinstanceRepository->update($instanceId, [
                'worked_hours' => $workedHours,
            ]);

            return $updateResult;
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Update planned hours for a task instance (add additional hours)
     * Also updates the parent Task's duration
     */
    public function updateplannedhours($instanceId, $plannedHours)
    {
        try {
            $instance = $this->taskinstanceRepository->getbyid($instanceId);

            if (! $instance) {
                return ['status' => 'error', 'message' => 'Task instance not found'];
            }

            if ($instance->status !== 'ongoing') {
                return ['status' => 'error', 'message' => 'Can only update planned hours for ongoing instances'];
            }

            // Calculate the additional hours being added
            $additionalHours = $plannedHours - $instance->planned_hours;

            // Update the task instance
            $updateResult = $this->taskinstanceRepository->update($instanceId, [
                'planned_hours' => $plannedHours,
            ]);

            // Also update the parent Task's duration
            if ($additionalHours > 0) {
                $task = Task::find($instance->task_id);
                if ($task) {
                    $task->duration = $task->duration + $additionalHours;
                    $task->save();
                }
            }

            return $updateResult;
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Rollover a task instance to the next day
     * 1. Mark current instance as 'rolled_over'
     * 2. Move the Task to the next calendar day
     * 3. Create new instance for next day with carried forward hours
     */
    public function rolloverinstance($instanceId, $nextDate = null)
    {
        try {
            $instance = $this->taskinstanceRepository->getbyid($instanceId);

            if (! $instance) {
                return ['status' => 'error', 'message' => 'Task instance not found'];
            }

            if ($instance->status === 'rolled_over') {
                return ['status' => 'error', 'message' => 'Task instance has already been rolled over'];
            }

            if ($instance->status === 'completed') {
                return ['status' => 'error', 'message' => 'Cannot rollover a completed task instance'];
            }

            // Calculate unworked hours to carry forward
            $unworkedHours = max(0, $instance->planned_hours - $instance->worked_hours);

            // Determine next date (default to next weekday)
            $nextDateCarbon = $nextDate ? Carbon::parse($nextDate) : Carbon::parse($instance->date)->addDay();

            // Skip weekends - move to Monday if Saturday or Sunday
            while ($nextDateCarbon->isWeekend()) {
                $nextDateCarbon->addDay();
            }
            $nextDateFormatted = $nextDateCarbon->format('Y-m-d');

            // Find or get the calendar day for the next date
            $nextCalendarDay = Calendarday::where('maindate', $nextDateFormatted)->first();

            if (! $nextCalendarDay) {
                return ['status' => 'error', 'message' => 'Calendar day not found for '.$nextDateFormatted.'. Please ensure the calendar is initialized.'];
            }

            // Mark current instance as rolled_over (this becomes history)
            $this->taskinstanceRepository->update($instanceId, [
                'status' => 'rolled_over',
            ]);

            // Move the Task to the next calendar day
            $task = Task::find($instance->task_id);
            if ($task) {
                $task->calendarday_id = $nextCalendarDay->id;
                $task->save();
            }

            // Create new instance for next day with carried forward hours
            $newInstanceResult = $this->taskinstanceRepository->create([
                'task_id' => $instance->task_id,
                'date' => $nextDateFormatted,
                'planned_hours' => $unworkedHours,
                'worked_hours' => 0,
                'status' => 'ongoing',
            ]);

            if ($newInstanceResult['status'] === 'error') {
                return $newInstanceResult;
            }

            return [
                'status' => 'success',
                'message' => 'Task rolled over successfully',
                'data' => [
                    'old_instance' => $instance,
                    'new_instance' => $newInstanceResult['data'],
                    'carried_forward_hours' => $unworkedHours,
                    'new_calendar_day' => $nextCalendarDay,
                ],
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Mark a task instance as completed
     */
    public function completeinstance($instanceId)
    {
        try {
            $instance = $this->taskinstanceRepository->getbyid($instanceId);

            if (! $instance) {
                return ['status' => 'error', 'message' => 'Task instance not found'];
            }

            if ($instance->status === 'completed') {
                return ['status' => 'error', 'message' => 'Task instance is already completed'];
            }

            $updateResult = $this->taskinstanceRepository->update($instanceId, [
                'status' => 'completed',
            ]);

            return $updateResult;
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
