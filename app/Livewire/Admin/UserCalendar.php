<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\ICalendarService;
use App\Models\Individualworkplan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class UserCalendar extends Component
{
    use Toast;

    protected $repository;

    protected $workplanrepository;

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

    protected $calendarService;

    public function boot(ICalendarService $calendarService, itaskInterface $repository, individualworkplanInterface $workplanrepository)
    {
        $this->calendarService = $calendarService;
        $this->repository = $repository;
        $this->workplanrepository = $workplanrepository;
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
            $output = strlen($outputText) > 80 ? substr($outputText, 0, 80) . '...' : $outputText;
            $indicator = strlen($indicatorText) > 80 ? substr($indicatorText, 0, 80) . '...' : $indicatorText;
            
            // Format month name for better readability
            $monthNames = [
                'Q1' => 'Q1', 'Q2' => 'Q2', 'Q3' => 'Q3', 'Q4' => 'Q4'
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
        $this->reset([
            'title',
            'priority',
            'duration',
            'uom',
            'description',
            'link',
            'individualworkplan_id',
            'id',
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
        } else {
            $this->error($response['message']);
        }
        $this->markmodal = false;
    }

    public function marktaskascompleted($id)
    {
        $response = $this->repository->marktask($id, 'completed');
        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the current week data to reflect updated approval status
            if ($this->week_id) {
                $this->getcalenderuserweektasksbyweekid();
            } else {
                $this->getcalenderuserweektasks();
            }
        } else {
            $this->error($response['message']);
        }
        $this->markmodal = false;
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
            if ($day->relationLoaded('tasks')) {
                $allTasks = $allTasks->merge($day->tasks);
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
