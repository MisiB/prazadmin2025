<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\icalendarInterface;
use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\itaskinstanceInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\ICalendarService;
use App\Interfaces\services\itaskinstanceService;
use App\Interfaces\services\itaskTemplateService;
use App\Models\Individualworkplan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class UserCalendar extends Component
{
    use Toast;
    use WithFileUploads;

    protected $repository;

    protected $workplanrepository;

    protected $taskinstanceService;

    public $startDate;

    public $endDate;

    public $currentweek = null;

    public $modal = false;

    public $currentday = null;

    public $title;

    public $priority;

    public $year;

    public $individualworkplan_id;

    public $description;

    public $status;

    public $duration;

    public $uom;

    public $start_date;

    public $end_date;

    public bool $link = false;

    public $id;

    public $selectedtask = null;

    public $viewtaskmodal = false;

    public $markmodal = false;

    public $taskid;

    public $week_id;

    public bool $viewcommentmodal = false;

    // TaskInstance properties
    public bool $logHoursModal = false;

    public $loggingTaskId = null;

    public $workedHours = 0;

    public $additionalHours = 0;  // NEW: for extending planned hours

    // Evidence upload properties
    public $evidenceFile = null;

    public bool $showEvidenceModal = false;

    public $completingTaskId = null;

    protected $calendarService;

    protected $taskTemplateService;

    public $templates = [];

    public $selectedTemplateId = null;

    public $saveAsTemplate = false;

    public bool $dayTasksModal = false;

    public $selectedDayId = null;

    public $selectedDayTasks = null;

    public $selectedDayTitle = null;

    // Bulk operations properties
    public array $selectedTaskIds = [];

    protected $calendarRepository;

    protected $taskinstanceRepository;

    public function boot(ICalendarService $calendarService, itaskInterface $repository, individualworkplanInterface $workplanrepository, itaskinstanceService $taskinstanceService, itaskTemplateService $taskTemplateService, icalendarInterface $calendarRepository, itaskinstanceInterface $taskinstanceRepository)
    {
        $this->calendarService = $calendarService;
        $this->repository = $repository;
        $this->workplanrepository = $workplanrepository;
        $this->taskinstanceService = $taskinstanceService;
        $this->taskTemplateService = $taskTemplateService;
        $this->calendarRepository = $calendarRepository;
        $this->taskinstanceRepository = $taskinstanceRepository;
    }

    public function mount()
    {
        $this->getcalenderuserweektasks();
        $this->year = Carbon::now()->year;
    }

    public function getcalenderuserweektasks()
    {
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        $this->currentweek = $this->calendarService->getcalenderuserweektasks($this->startDate, $this->endDate);

    }

    public function getcalenderuserweektasksbyweekid()
    {
        $this->currentweek = $this->calendarService->getusercalendarweektasks($this->week_id);
    }

    public function updatedweekid()
    {
        $this->getcalenderuserweektasksbyweekid();
    }

    public function getweeks()
    {
        return $this->calendarService->getweeks($this->year);
    }

    public function openModal($day)
    {
        $this->currentday = $day;
        $this->modal = true;
        $this->loadTemplates();
        $this->selectedTemplateId = null;
        $this->saveAsTemplate = false;
    }

    public function openDayModal($dayId)
    {
        $day = $this->calendarRepository->getcalendardaybyid($dayId);

        if ($day) {
            $this->selectedDayId = $dayId;
            $tasks = $day->userTasks ?? collect();

            // Group tasks by status
            $this->selectedDayTasks = $this->groupTasksByStatus($tasks);
            $this->selectedDayTitle = Carbon::parse($day->maindate)->format('l, F d, Y');
            $this->dayTasksModal = true;
        }
    }

    /**
     * Group tasks by status for better organization
     */
    private function groupTasksByStatus($tasks)
    {
        $grouped = [
            'rejected' => collect(),
            'pending_approval' => collect(),
            'pending' => collect(),
            'ongoing' => collect(),
            'completed' => collect(),
        ];

        foreach ($tasks as $task) {
            // Rejected tasks (highest priority - needs immediate attention)
            if ($task->approvalstatus == 'Rejected') {
                $grouped['rejected']->push($task);
            }
            // Pending approval (completed tasks waiting for approval)
            elseif ($task->status == 'completed' && $task->approvalstatus == 'pending') {
                $grouped['pending_approval']->push($task);
            }
            // Pending tasks (not started yet)
            elseif ($task->status == 'pending') {
                $grouped['pending']->push($task);
            }
            // Ongoing tasks (in progress)
            elseif ($task->status == 'ongoing') {
                $grouped['ongoing']->push($task);
            }
            // Completed and approved tasks
            else {
                $grouped['completed']->push($task);
            }
        }

        return $grouped;
    }

    public function closeDayModal()
    {
        $this->dayTasksModal = false;
        $this->selectedDayId = null;
        $this->selectedDayTasks = null;
        $this->selectedDayTitle = null;
        $this->selectedTaskIds = []; // Reset selections
    }

    /**
     * Refresh the day modal data if it's currently open
     */
    public function refreshDayModalIfOpen(): void
    {
        if ($this->dayTasksModal && $this->selectedDayId) {
            $day = $this->calendarRepository->getcalendardaybyid($this->selectedDayId);
            if ($day) {
                $tasks = $day->userTasks ?? collect();
                
                // Reload taskinstances relationship on each task to get fresh data
                foreach ($tasks as $task) {
                    $task->load('taskinstances');
                }
                
                $this->selectedDayTasks = $this->groupTasksByStatus($tasks);
            }
        }
    }

    public function getmyindividualworkplans()
    {
        // Get all approved individual workplans for the current user in the current year
        $workplans = Individualworkplan::with('user', 'targetmatrix.target.indicator.departmentoutput.output.outcome.programme')
            ->where('user_id', Auth::user()->id)
            ->where('year', $this->year)
            ->get();

        return $workplans->map(function ($workplan) {
            // Get output text from relationship or fallback to direct field
            $outputText = $workplan->targetmatrix?->target?->indicator?->departmentoutput?->output?->title
            ?? $workplan->output;

            // Get indicator text from relationship or fallback to direct field
            $indicatorText = $workplan->targetmatrix?->target?->indicator?->title
                ?? $workplan->indicator;

            // Truncate long outputs and indicators for better readability in dropdown
            $output = strlen($outputText) > 80 ? substr($outputText, 0, 80).'...' : $outputText;
            $indicator = strlen($indicatorText) > 80 ? substr($indicatorText, 0, 80).'...' : $indicatorText;

            // Format month name for better readability
            $monthNames = [
                'Q1' => 'Q1', 'Q2' => 'Q2', 'Q3' => 'Q3', 'Q4' => 'Q4',
            ];
            $month = $monthNames[$workplan->month] ?? $workplan->month;

            return [
                'id' => $workplan->id,
                'description' => sprintf(
                    '[%s] %s | %s | Target: %s | Weightage: %s%%',
                    $month,
                    $output,
                    $indicator,
                    $workplan->target,
                    $workplan->weightage
                ),
            ];
        })->values();
    }

    public function statuslist(): array
    {
        return [
            ['id' => 'pending', 'name' => 'Pending'],
            ['id' => 'ongoing', 'name' => 'Ongoing'],
            ['id' => 'completed', 'name' => 'Completed'],
        ];
    }

    public function prioritylist(): array
    {
        return [
            ['id' => 'High', 'name' => 'High'],
            ['id' => 'Medium', 'name' => 'Medium'],
            ['id' => 'Low', 'name' => 'Low'],
        ];
    }

    public function loadTemplates()
    {
        $this->templates = $this->taskTemplateService->getmytemplates(Auth::user()->id);
    }

    public function updatedSelectedTemplateId()
    {
        if ($this->selectedTemplateId) {
            $template = $this->taskTemplateService->gettemplate($this->selectedTemplateId);
            if ($template) {
                $this->title = $template->title;
                $this->description = $template->description;
                $this->priority = $template->priority;
                $this->duration = $template->duration;
                $this->uom = $template->uom;
                $this->individualworkplan_id = $template->individualworkplan_id;
                $this->link = ! is_null($template->individualworkplan_id);
            }
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required',
            'priority' => 'required',
            'description' => 'required',
            'duration' => 'required',
            'uom' => 'required',
            'individualworkplan_id' => 'required_if:link,true',

        ]);
        if ($this->id) {
            $this->update();
        } else {
            $this->create();
        }

        // Save as template if checkbox is checked
        if ($this->saveAsTemplate && ! $this->id) {
            $templateResult = $this->taskTemplateService->createtemplate([
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'duration' => $this->duration,
                'uom' => $this->uom,
                'individualworkplan_id' => $this->individualworkplan_id,
            ]);

            if ($templateResult['status'] === 'success') {
                $this->success('Task created and saved as template');
            }
        }

        $this->reset([
            'title',
            'priority',
            'duration',
            'uom',
            'description',
            'link',
            'individualworkplan_id',
            'id',
            'selectedTemplateId',
            'saveAsTemplate',
        ]);
    }

    public function create()
    {
        $response = $this->repository->createtask([
            'title' => $this->title,
            'priority' => $this->priority,
            'description' => $this->description,
            'calendarday_id' => $this->currentday,
            'user_id' => Auth::user()->id,
            'individualworkplan_id' => $this->individualworkplan_id,
            'duration' => $this->duration,
            'uom' => $this->uom,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect new task
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($response['message']);
        }
    }

    public function edit($id)
    {

        $task = $this->repository->gettask($id);
        $this->id = $task->id;
        $this->title = $task->title;
        $this->currentday = $task->calendarday_id;
        $this->priority = $task->priority;
        $this->individualworkplan_id = $task->individualworkplan_id;
        $this->link = ! is_null($task->individualworkplan_id);
        $this->description = $task->description;
        $this->duration = $task->duration;
        $this->uom = $task->uom;
        $this->status = $task->status;
        $this->modal = true;
    }

    public function update()
    {
        $response = $this->repository->updatetask($this->id, [
            'title' => $this->title,
            'priority' => $this->priority,
            'description' => $this->description,
            'calendarday_id' => $this->currentday,
            'user_id' => Auth::user()->id,
            'duration' => $this->duration,
            'uom' => $this->uom,
            'individualworkplan_id' => $this->individualworkplan_id,
        ]);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect updated approval status
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->repository->deletetask($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect deleted task
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($response['message']);
        }
    }

    public function viewtask($id)
    {
        $this->selectedtask = null;
        $task = $this->repository->gettask($id);
        $this->selectedtask = $task;
        $this->viewtaskmodal = true;
    }

    public function openmarkmodal($id)
    {
        $this->taskid = $id;
        $this->markmodal = true;
    }

    public function marktask($id)
    {
        $response = $this->repository->marktask($id, 'completed');
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function marktaskasongoing($id)
    {
        $response = $this->repository->marktask($id, 'ongoing');
        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect updated status
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($response['message']);
        }
        $this->markmodal = false;
    }

    public function marktaskaspending($id)
    {
        $response = $this->repository->marktask($id, 'pending');
        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect updated status
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($response['message']);
        }
        $this->markmodal = false;
    }

    /**
     * Open evidence upload modal for completing a task
     */
    public function openEvidenceModal($taskId)
    {
        $this->completingTaskId = $taskId;
        $this->evidenceFile = null;
        $this->showEvidenceModal = true;
        $this->markmodal = false;
    }

    /**
     * Complete task without evidence
     */
    public function completeWithoutEvidence()
    {
        $this->evidenceFile = null;
        $this->marktaskascompleted($this->completingTaskId);
    }

    /**
     * Mark task as completed with optional evidence upload
     */
    public function marktaskascompleted($id)
    {
        $evidencePath = null;
        $originalName = null;

        // Handle file upload if provided
        if ($this->evidenceFile) {
            $this->validate([
                'evidenceFile' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
            ]);

            $originalName = $this->evidenceFile->getClientOriginalName();
            $evidencePath = $this->evidenceFile->store('task-evidence', 'public');
        }

        $response = $this->repository->marktask($id, 'completed', $evidencePath, $originalName);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect updated approval status
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($response['message']);
        }

        $this->showEvidenceModal = false;
        $this->markmodal = false;
        $this->evidenceFile = null;
        $this->completingTaskId = null;
    }

    public function sendforapproval()
    {
        $response = $this->calendarService->sendforapproval($this->currentweek->id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect updated status
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
        } else {
            $this->error($response['message']);
        }
    }

    public function getTaskSummary(): array
    {
        $allTasks = collect();

        // Collect all tasks from all calendar days
        foreach ($this->currentweek->calendardays as $day) {
            if ($day->relationLoaded('userTasks')) {
                $allTasks = $allTasks->merge($day->userTasks);
            } elseif ($day->relationLoaded('tasks')) {
                // Fallback: filter tasks by current user if userTasks not loaded
                $allTasks = $allTasks->merge($day->tasks->where('user_id', Auth::user()->id));
            }
        }

        $summary = [
            'total' => $allTasks->count(),
            'pending' => $allTasks->where('status', 'pending')->count(),
            'ongoing' => $allTasks->where('status', 'ongoing')->count(),
            'completed' => $allTasks->where('status', 'completed')->count(),
            'total_hours' => $allTasks->sum('duration'),
            'pending_hours' => $allTasks->where('status', 'pending')->sum('duration'),
            'ongoing_hours' => $allTasks->where('status', 'ongoing')->sum('duration'),
            'completed_hours' => $allTasks->where('status', 'completed')->sum('duration'),
        ];

        return $summary;
    }

    // TaskInstance Methods

    /**
     * Get the active task instance for a task
     */
    public function getActiveTaskInstance($taskId)
    {
        return $this->taskinstanceRepository->getactiveinstancebytaskid($taskId);
    }

    /**
     * Open the log hours modal
     */
    public function openLogHoursModal($taskId)
    {
        $this->loggingTaskId = $taskId;
        $instance = $this->getActiveTaskInstance($taskId);
        $this->workedHours = $instance ? $instance->worked_hours : 0;
        $this->additionalHours = 0;  // Reset additional hours
        $this->logHoursModal = true;
    }

    /**
     * Close the log hours modal
     */
    public function closeLogHoursModal()
    {
        $this->logHoursModal = false;
        $this->loggingTaskId = null;
        $this->workedHours = 0;
        $this->additionalHours = 0;  // NEW
    }

    /**
     * Log worked hours for a task
     */
    public function logHours()
    {
        $this->validate([
            'workedHours' => 'required|numeric|min:0',
            'additionalHours' => 'nullable|numeric|min:0',
        ]);

        $instance = $this->getActiveTaskInstance($this->loggingTaskId);

        if (! $instance) {
            $this->error('No active task instance found. Please mark the task as ongoing first.');
            return;
        }

        // Log the worked hours
        $result = $this->taskinstanceService->loghours($instance->id, $this->workedHours);

        if ($result['status'] === 'success') {
            // If additional hours were requested, add them to planned hours
            if ($this->additionalHours > 0) {
                $newPlannedHours = $instance->planned_hours + $this->additionalHours;
                $this->taskinstanceService->updateplannedhours($instance->id, $newPlannedHours);
            }

            $this->success('Hours logged successfully');
            $this->closeLogHoursModal();
            // Refresh current week data
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * Rollover a task to the next day
     */
    public function rolloverTask($taskId)
    {
        $instance = $this->getActiveTaskInstance($taskId);

        if (! $instance) {
            $this->error('No active task instance found to rollover');

            return;
        }

        $result = $this->taskinstanceService->rolloverinstance($instance->id);

        if ($result['status'] === 'success') {
            $carriedHours = $result['data']['carried_forward_hours'] ?? 0;
            $this->success("Task rolled over. {$carriedHours} hours carried forward to tomorrow.");
            // Refresh current week data
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * Complete a task instance
     */
    public function completeTaskInstance($taskId)
    {
        $instance = $this->getActiveTaskInstance($taskId);

        if (! $instance) {
            $this->error('No active task instance found');

            return;
        }

        $result = $this->taskinstanceService->completeinstance($instance->id);

        if ($result['status'] === 'success') {
            $this->success('Task instance marked as completed');
            // Refresh current week data
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
            // Refresh day modal if open
            $this->refreshDayModalIfOpen();
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * Toggle task selection for bulk operations
     */
    public function toggleTaskSelection($taskId)
    {
        if (in_array($taskId, $this->selectedTaskIds)) {
            $this->selectedTaskIds = array_values(array_diff($this->selectedTaskIds, [$taskId]));
        } else {
            $this->selectedTaskIds[] = $taskId;
        }
    }

    /**
     * Select all tasks in the current day modal
     */
    public function selectAllTasks()
    {
        $this->selectedTaskIds = [];

        if ($this->selectedDayTasks) {
            foreach ($this->selectedDayTasks as $group) {
                foreach ($group as $task) {
                    // Only select tasks that can be rolled over or have status updated
                    if ($task->status != 'completed' && $task->approvalstatus != 'Rejected') {
                        $this->selectedTaskIds[] = $task->id;
                    }
                }
            }
        }
    }

    /**
     * Deselect all tasks
     */
    public function deselectAllTasks()
    {
        $this->selectedTaskIds = [];
    }

    /**
     * Check if a task is eligible for rollover
     * Eligible: pending status OR ongoing status with logged hours
     */
    private function isTaskEligibleForRollover($taskId)
    {
        $task = $this->repository->gettask($taskId);

        if (! $task) {
            return false;
        }

        // Pending tasks are always eligible
        if ($task->status === 'pending') {
            return true;
        }

        // Ongoing tasks are only eligible if they have logged hours
        if ($task->status === 'ongoing') {
            $instance = $this->getActiveTaskInstance($taskId);
            if ($instance && $instance->worked_hours > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bulk rollover selected tasks
     */
    public function bulkRollover()
    {
        if (empty($this->selectedTaskIds)) {
            $this->error('Please select at least one task to rollover');

            return;
        }

        // Filter to only eligible tasks
        $eligibleTaskIds = [];
        $ineligibleTasks = [];

        foreach ($this->selectedTaskIds as $taskId) {
            if ($this->isTaskEligibleForRollover($taskId)) {
                $eligibleTaskIds[] = $taskId;
            } else {
                $task = $this->repository->gettask($taskId);
                $ineligibleTasks[] = $task ? $task->title : "Task ID {$taskId}";
            }
        }

        if (empty($eligibleTaskIds)) {
            $this->error('No eligible tasks selected. Only pending tasks or ongoing tasks with logged hours can be rolled over.');

            return;
        }

        if (! empty($ineligibleTasks)) {
            $this->warning(count($ineligibleTasks).' task(s) were skipped because they are not eligible for rollover: '.implode(', ', array_slice($ineligibleTasks, 0, 3)));
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($eligibleTaskIds as $taskId) {
            $task = $this->repository->gettask($taskId);
            $instance = $this->getActiveTaskInstance($taskId);

            // For pending tasks, we need to handle the case where there might not be an instance yet
            if (! $instance) {
                if ($task && $task->status === 'pending') {
                    // Pending tasks without instance cannot be rolled over yet
                    $errorCount++;
                    $errors[] = "Task '{$task->title}': Cannot rollover pending task without active instance";

                    continue;
                }

                $errorCount++;
                $errors[] = "Task ID {$taskId}: No active instance found";

                continue;
            }

            $result = $this->taskinstanceService->rolloverinstance($instance->id);

            if ($result['status'] === 'success') {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = "Task '{$task->title}': {$result['message']}";
            }
        }

        // Show results
        if ($successCount > 0) {
            $this->success("Successfully rolled over {$successCount} task(s)");
        }

        if ($errorCount > 0) {
            $this->error("Failed to rollover {$errorCount} task(s). ".implode('; ', array_slice($errors, 0, 3)));
        }

        // Reset selections
        $this->selectedTaskIds = [];

        // Refresh data
        if ($this->week_id) {
            $this->getcalenderuserweektasksbyweekid();
        } else {
            $this->getcalenderuserweektasks();
        }
        $this->refreshDayModalIfOpen();
    }

    public function render()
    {
        return view('livewire.admin.user-calendar', [
            'workplanlist' => $this->getmyindividualworkplans(),
            'statuslist' => $this->statuslist(),
            'prioritylist' => $this->prioritylist(),
            'weeks' => $this->getweeks(),
            'taskSummary' => $this->getTaskSummary(),
        ]);
    }
}
