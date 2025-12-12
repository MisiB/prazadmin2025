<?php

namespace App\implementation\services;

use App\Interfaces\repositories\irecurringTaskInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\irecurringTaskService;
use App\Models\Calendarday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class _recurringTaskService implements irecurringTaskService
{
    protected $recurringTaskRepository;

    protected $taskRepository;

    public function __construct(irecurringTaskInterface $recurringTaskRepository, itaskInterface $taskRepository)
    {
        $this->recurringTaskRepository = $recurringTaskRepository;
        $this->taskRepository = $taskRepository;
    }

    public function getmyrecurringtasks($userId)
    {
        return $this->recurringTaskRepository->getmyrecurringtasks($userId);
    }

    public function getrecurringtask($id)
    {
        return $this->recurringTaskRepository->getrecurringtask($id);
    }

    public function createrecurringtask(array $data)
    {
        $data['user_id'] = Auth::user()->id;

        // If using a template, populate fields from template
        if (isset($data['task_template_id']) && $data['task_template_id']) {
            $template = \App\Models\TaskTemplate::find($data['task_template_id']);
            if ($template) {
                $data['title'] = $template->title;
                $data['description'] = $template->description;
                $data['priority'] = $template->priority;
                $data['duration'] = $template->duration;
                $data['uom'] = $template->uom;
                $data['individualworkplan_id'] = $template->individualworkplan_id;
            }
        }

        return $this->recurringTaskRepository->createrecurringtask($data);
    }

    public function updaterecurringtask($id, array $data)
    {
        return $this->recurringTaskRepository->updaterecurringtask($id, $data);
    }

    public function deleterecurringtask($id)
    {
        return $this->recurringTaskRepository->deleterecurringtask($id);
    }

    /**
     * Process all active recurring tasks and create tasks as needed
     * This is called by the scheduled command
     */
    public function processrecurringtasks()
    {
        $recurringTasks = $this->recurringTaskRepository->getactiverecurringtasks();
        $createdCount = 0;
        $errors = [];

        foreach ($recurringTasks as $recurringTask) {
            try {
                // Check if task should be created today
                $today = Carbon::today();
                $shouldCreate = false;

                switch ($recurringTask->frequency) {
                    case 'daily':
                        $shouldCreate = ! $today->isWeekend();
                        break;

                    case 'weekly':
                        $dayOfWeek = $recurringTask->day_of_week ?? 1;
                        $shouldCreate = $today->dayOfWeek == $dayOfWeek;
                        break;

                    case 'monthly':
                        $dayOfMonth = $recurringTask->day_of_month ?? 1;
                        $lastDayOfMonth = $today->copy()->endOfMonth()->day;
                        $adjustedDay = min($dayOfMonth, $lastDayOfMonth);
                        $shouldCreate = $today->day == $adjustedDay && ! $today->isWeekend();
                        break;
                }

                if ($shouldCreate && $recurringTask->next_create_date <= $today) {
                    // Find or get calendar day for today
                    $calendarday = Calendarday::where('maindate', $today->format('Y-m-d'))->first();

                    if ($calendarday) {
                        // Create task from recurring task
                        $taskData = [
                            'title' => $recurringTask->title,
                            'description' => $recurringTask->description,
                            'priority' => $recurringTask->priority,
                            'duration' => $recurringTask->duration,
                            'uom' => $recurringTask->uom,
                            'user_id' => $recurringTask->user_id,
                            'calendarday_id' => $calendarday->id,
                            'individualworkplan_id' => $recurringTask->individualworkplan_id,
                        ];

                        $result = $this->taskRepository->createtask($taskData);

                        if ($result['status'] === 'success') {
                            // Update recurring task
                            $this->recurringTaskRepository->updatelastcreateddate($recurringTask->id, $today->format('Y-m-d'));
                            $createdCount++;
                        } else {
                            $errors[] = "Failed to create task for recurring task #{$recurringTask->id}: {$result['message']}";
                        }
                    } else {
                        $errors[] = "Calendar day not found for {$today->format('Y-m-d')}";
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing recurring task #{$recurringTask->id}: {$e->getMessage()}";
            }
        }

        return [
            'status' => 'success',
            'created_count' => $createdCount,
            'errors' => $errors,
            'message' => "Processed {$createdCount} recurring task(s)",
        ];
    }
}
