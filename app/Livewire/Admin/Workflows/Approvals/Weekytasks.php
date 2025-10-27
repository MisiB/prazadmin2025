<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\iworkplanInterface;
use App\Interfaces\services\ICalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Weekytasks extends Component
{
    public $breadcrumbs =[];
    protected $calendarService;
    protected $repository;
    protected $workplanrepository;
    public $year;
    public $startDate;
    public $endDate;
    public $showModal = false;
    public $selectedUser = null;
    public $selectedUserTasks = [];
    public function boot(ICalendarService $calendarService,itaskInterface $repository,iworkplanInterface $workplanrepository)
    {
        $this->calendarService = $calendarService;
        $this->repository = $repository;
        $this->workplanrepository = $workplanrepository;
    }

    public function mount(){
        $this->year = Carbon::now()->year;
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Weekly departmental tasks']
        ];
        
 
    }

    public function getweeks(){
        return $this->calendarService->getweeks($this->year);
    }
    public function gettasksbydepartment(){ 
        $department_id = Auth::user()->department->department_id;
        $data= $this->calendarService->gettasksbydepartment($department_id,$this->startDate,$this->endDate);
        
        // Handle case where no calendar week is found
        if($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) {
            return [
                'users' => collect(),
                'calendarweek' => null
            ];
        }
        
        return $data;
    }

    public function getTotalTasksCount($users) {
        return $users->sum(function($user) { 
            return $user->calenderworkusertasks->sum(function($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                    return $calendarday->tasks->count();
                });
            });
        });
    }

    public function getUsersWithTasksCount($users) {
        return $users->filter(function($user) { 
            $actualTasksCount = $user->calenderworkusertasks->sum(function($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                    return $calendarday->tasks->count();
                });
            });
            return $actualTasksCount > 0; 
        })->count();
    }

    public function getUserTaskCount($user) {
        return $user->calenderworkusertasks->sum(function($calenderworkusertask) {
            return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                return $calendarday->tasks->count();
            });
        });
    }

    public function getUserActualTasks($user) {
        $tasks = collect();
        $user->calenderworkusertasks->each(function($calenderworkusertask) use ($tasks) {
            $calenderworkusertask->calendarweek->calendardays->each(function($calendarday) use ($tasks) {
                $tasks->push(...$calendarday->tasks);
            });
        });
        return $tasks;
    }

    public function getLinkedTasksCount($users) {
        return $users->sum(function($user) { 
            return $user->calenderworkusertasks->sum(function($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                    return $calendarday->tasks->whereNotNull('individualoutputbreakdown_id')->count();
                });
            });
        });
    }

    public function getUnlinkedTasksCount($users) {
        return $users->sum(function($user) { 
            return $user->calenderworkusertasks->sum(function($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                    return $calendarday->tasks->whereNull('individualoutputbreakdown_id')->count();
                });
            });
        });
    }

    public function getLinkedTasksPercentage($users) {
        $totalTasks = $this->getTotalTasksCount($users);
        if ($totalTasks == 0) return 0;
        
        $linkedTasks = $this->getLinkedTasksCount($users);
        return round(($linkedTasks / $totalTasks) * 100, 1);
    }

    public function getUserLinkedTasksCount($user) {
        return $user->calenderworkusertasks->sum(function($calenderworkusertask) {
            return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                return $calendarday->tasks->whereNotNull('individualoutputbreakdown_id')->count();
            });
        });
    }

    public function getUserUnlinkedTasksCount($user) {
        return $user->calenderworkusertasks->sum(function($calenderworkusertask) {
            return $calenderworkusertask->calendarweek->calendardays->sum(function($calendarday) {
                return $calendarday->tasks->whereNull('individualoutputbreakdown_id')->count();
            });
        });
    }

    public function openTaskModal($userId) {
        $users = $this->gettasksbydepartment()['users'];
        $this->selectedUser = $users->find($userId);
        $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        $this->showModal = true;
    }

    public function closeModal() {
        $this->showModal = false;
        $this->selectedUser = null;
        $this->selectedUserTasks = [];
    }

    public function getUsersByDepartment($users) {
        return $users->groupBy(function($user) {
            return $user->department ? $user->department->department->name : 'No Department';
        });
    }

    public function getDepartmentStats($users) {
        $departments = $this->getUsersByDepartment($users);
        $stats = collect();
        
        foreach($departments as $deptName => $deptUsers) {
            $totalTasks = $deptUsers->sum(function($user) {
                return $this->getUserTaskCount($user);
            });
            $linkedTasks = $deptUsers->sum(function($user) {
                return $this->getUserLinkedTasksCount($user);
            });
            $unlinkedTasks = $deptUsers->sum(function($user) {
                return $this->getUserUnlinkedTasksCount($user);
            });
            $linkedPercentage = $totalTasks > 0 ? round(($linkedTasks / $totalTasks) * 100, 1) : 0;
            
            $stats->push([
                'name' => $deptName,
                'users' => $deptUsers,
                'total_users' => $deptUsers->count(),
                'total_tasks' => $totalTasks,
                'linked_tasks' => $linkedTasks,
                'unlinked_tasks' => $unlinkedTasks,
                'linked_percentage' => $linkedPercentage,
                'users_with_tasks' => $deptUsers->filter(function($user) {
                    return $this->getUserTaskCount($user) > 0;
                })->count()
            ]);
        }
        
        return $stats;
    }
    public function render()
    {
        return view('livewire.admin.workflows.approvals.weekytasks',[
            'weeks'=>$this->getweeks(),
            'tasks'=>$this->gettasksbydepartment()
        ]);
    }
}
 