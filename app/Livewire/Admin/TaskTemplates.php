<?php

namespace App\Livewire\Admin;

use App\Interfaces\services\itaskTemplateService;
use Illuminate\Support\Facades\Auth;
use App\Models\Individualworkplan;
use Livewire\Component;
use Mary\Traits\Toast;

class TaskTemplates extends Component
{
    use Toast;

    protected $taskTemplateService;

    public $breadcrumbs = [];

    public $year;

    public $templates = [];

    public $showModal = false;

    public $editingId = null;

    public $title = '';

    public $description = '';

    public $priority = 'Medium';

    public $duration = 0;

    public $uom = 'hours';

    public $individualworkplan_id = null;

    public $workplans = [];

    public function boot(itaskTemplateService $taskTemplateService)
    {
        $this->taskTemplateService = $taskTemplateService;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Task Templates'],
        ];
        $this->year = date('Y');
        $this->loadTemplates();
        $this->loadWorkplans();
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

    public function openModal($templateId = null)
    {
        $this->editingId = $templateId;
        $this->resetForm();

        if ($templateId) {
            $template = $this->taskTemplateService->gettemplate($templateId);
            if ($template) {
                $this->title = $template->title;
                $this->description = $template->description;
                $this->priority = $template->priority;
                $this->duration = $template->duration;
                $this->uom = $template->uom;
                $this->individualworkplan_id = $template->individualworkplan_id;
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
        $this->title = '';
        $this->description = '';
        $this->priority = 'Medium';
        $this->duration = 0;
        $this->uom = 'hours';
        $this->individualworkplan_id = null;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
            'duration' => 'required|numeric|min:0',
            'uom' => 'required|string',
            'individualworkplan_id' => 'nullable|exists:individualworkplans,id',
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'duration' => $this->duration,
            'uom' => $this->uom,
            'individualworkplan_id' => $this->individualworkplan_id,
        ];

        if ($this->editingId) {
            $result = $this->taskTemplateService->updatetemplate($this->editingId, $data);
        } else {
            $result = $this->taskTemplateService->createtemplate($data);
        }

        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->closeModal();
            $this->loadTemplates();
        } else {
            $this->error($result['message']);
        }
    }

    public function delete($id)
    {
        $result = $this->taskTemplateService->deletetemplate($id);

        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->loadTemplates();
        } else {
            $this->error($result['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.task-templates');
    }
}
