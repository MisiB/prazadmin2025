<?php

namespace App\Livewire\Admin;

use App\Interfaces\services\irecurringTaskService;
use App\Interfaces\services\itaskTemplateService;
use App\Models\Individualworkplan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class RecurringTasks extends Component
{
    use Toast;

    protected $recurringTaskService;

    protected $taskTemplateService;

    public $breadcrumbs = [];

    public $year;

    public $recurringTasks = [];

    public $templates = [];

    public $showModal = false;

    public $editingId = null;

    public $task_template_id = null;

    public $title = '';

    public $description = '';

    public $priority = 'Medium';

    public $duration = 0;

    public $uom = 'hours';

    public $individualworkplan_id = null;

    public $frequency = 'daily';

    public $day_of_week = 1;

    public $day_of_month = 1;

    public $start_date;

    public $end_date = null;

    public $is_active = true;

    public $workplans = [];

    public function boot(irecurringTaskService $recurringTaskService, itaskTemplateService $taskTemplateService)
    {
        $this->recurringTaskService = $recurringTaskService;
        $this->taskTemplateService = $taskTemplateService;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Recurring Tasks'],
        ];
        $this->year = date('Y');
        $this->start_date = Carbon::today()->format('Y-m-d');
        $this->loadRecurringTasks();
        $this->loadTemplates();
        $this->loadWorkplans();
    }

    public function loadRecurringTasks()
    {
        $this->recurringTasks = $this->recurringTaskService->getmyrecurringtasks(Auth::user()->id);
    }

    public function loadTemplates()
    {
        $this->templates = $this->taskTemplateService->getmytemplates(Auth::user()->id);
    }

    public function loadWorkplans()
    {
        // Get all approved individual workplans for the current user in the current year
        $workplans = Individualworkplan::with('user', 'targetmatrix.target.indicator.departmentoutput.output.outcome.programme')
            ->where('user_id', Auth::user()->id)
            ->where('year', $this->year)
            ->get();

        $this->workplans = $workplans->map(function ($workplan) {
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
                'display_name' => sprintf(
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

    public function updatedTaskTemplateId()
    {
        if ($this->task_template_id) {
            $template = $this->taskTemplateService->gettemplate($this->task_template_id);
            if ($template) {
                $this->title = $template->title;
                $this->description = $template->description;
                $this->priority = $template->priority;
                $this->duration = $template->duration;
                $this->uom = $template->uom;
                $this->individualworkplan_id = $template->individualworkplan_id;
            }
        }
    }

    public function openModal($recurringTaskId = null)
    {
        $this->editingId = $recurringTaskId;
        $this->resetForm();

        if ($recurringTaskId) {
            $recurringTask = $this->recurringTaskService->getrecurringtask($recurringTaskId);
            if ($recurringTask) {
                $this->task_template_id = $recurringTask->task_template_id;
                $this->title = $recurringTask->title;
                $this->description = $recurringTask->description;
                $this->priority = $recurringTask->priority;
                $this->duration = $recurringTask->duration;
                $this->uom = $recurringTask->uom;
                $this->individualworkplan_id = $recurringTask->individualworkplan_id;
                $this->frequency = $recurringTask->frequency;
                $this->day_of_week = $recurringTask->day_of_week ?? 1;
                $this->day_of_month = $recurringTask->day_of_month ?? 1;
                $this->start_date = $recurringTask->start_date->format('Y-m-d');
                $this->end_date = $recurringTask->end_date ? $recurringTask->end_date->format('Y-m-d') : null;
                $this->is_active = $recurringTask->is_active;
            }
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->task_template_id = null;
        $this->title = '';
        $this->description = '';
        $this->priority = 'Medium';
        $this->duration = 0;
        $this->uom = 'hours';
        $this->individualworkplan_id = null;
        $this->frequency = 'daily';
        $this->day_of_week = 1;
        $this->day_of_month = 1;
        $this->start_date = Carbon::today()->format('Y-m-d');
        $this->end_date = null;
        $this->is_active = true;
    }

    public function save()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
            'duration' => 'required|numeric|min:0',
            'uom' => 'required|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ];

        if ($this->frequency === 'weekly') {
            $rules['day_of_week'] = 'required|integer|min:1|max:5';
        }

        if ($this->frequency === 'monthly') {
            $rules['day_of_month'] = 'required|integer|min:1|max:31';
        }

        $this->validate($rules);

        $data = [
            'task_template_id' => $this->task_template_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'duration' => $this->duration,
            'uom' => $this->uom,
            'individualworkplan_id' => $this->individualworkplan_id,
            'frequency' => $this->frequency,
            'day_of_week' => $this->frequency === 'weekly' ? $this->day_of_week : null,
            'day_of_month' => $this->frequency === 'monthly' ? $this->day_of_month : null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $result = $this->recurringTaskService->updaterecurringtask($this->editingId, $data);
        } else {
            $result = $this->recurringTaskService->createrecurringtask($data);
        }

        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->closeModal();
            $this->loadRecurringTasks();
        } else {
            $this->error($result['message']);
        }
    }

    public function delete($id)
    {
        $result = $this->recurringTaskService->deleterecurringtask($id);

        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->loadRecurringTasks();
        } else {
            $this->error($result['message']);
        }
    }

    public function toggleActive($id)
    {
        $recurringTask = $this->recurringTaskService->getrecurringtask($id);
        if ($recurringTask) {
            $result = $this->recurringTaskService->updaterecurringtask($id, [
                'is_active' => ! $recurringTask->is_active,
            ]);

            if ($result['status'] === 'success') {
                $this->success($recurringTask->is_active ? 'Recurring task deactivated' : 'Recurring task activated');
                $this->loadRecurringTasks();
            } else {
                $this->error($result['message']);
            }
        }
    }

    public function getDayName($dayOfWeek)
    {
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];

        return $days[$dayOfWeek] ?? 'Monday';
    }

    public function render()
    {
        return view('livewire.admin.recurring-tasks');
    }
}
