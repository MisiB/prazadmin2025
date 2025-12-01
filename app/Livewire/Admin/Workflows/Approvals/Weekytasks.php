<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\iworkplanInterface;
use App\Interfaces\services\ICalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Weekytasks extends Component
{
    use Toast;
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
    public $showBulkApprovalModal = false;
    public $bulkApprovalComment = '';
    public $bulkApprovalStatus = 'Approved';
    public $showCompletedBulkApprovalModal = false;
    public $completedBulkApprovalComment = '';
    public $completedBulkApprovalStatus = 'Approved';
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
                $calendarday->tasks->each(function($task) use ($tasks) {
                    $task->load('calendarday');
                    $tasks->push($task);
                });
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

    public function approveTask($taskId, $status) {
        $result = $this->repository->approvetask([
            'id' => $taskId,
            'status' => $status,
        ]);
        
        if ($result['status'] === 'success') {
            $this->success($result['message']);
            // Refresh the tasks
            if ($this->selectedUser) {
                $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
            }
        } else {
            $this->error($result['message']);
        }
    }

    public function openBulkApprovalModal($userId = null) {
        // If modal is already open, use existing selectedUser, otherwise load it
        if (!$this->selectedUser || ($userId && $this->selectedUser->id != $userId)) {
            $users = $this->gettasksbydepartment()['users'];
            $this->selectedUser = $users->find($userId);
            $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        }
        
        // Refresh tasks to ensure we have latest data
        if ($this->selectedUser) {
            $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        }
        
        // Check if there are pending tasks for initial submission
        $pendingInitialTasks = $this->selectedUserTasks->filter(function($task) {
            return $task->approvalstatus == 'pending' && $task->status != 'completed';
        });
        
        if ($pendingInitialTasks->isEmpty()) {
            $this->error('No pending tasks found for bulk approval');
            return;
        }
        
        $this->showBulkApprovalModal = true;
        $this->bulkApprovalComment = '';
        $this->bulkApprovalStatus = 'Approved';
    }

    public function closeBulkApprovalModal() {
        $this->showBulkApprovalModal = false;
        $this->bulkApprovalComment = '';
        $this->bulkApprovalStatus = 'Approved';
    }

    public function bulkApproveTasks() {
        $this->validate([
            'bulkApprovalComment' => 'required',
        ], [
            'bulkApprovalComment.required' => 'Please provide a comment for the approval decision.',
        ]);
        
        if (!$this->selectedUser || !$this->selectedUserTasks) {
            $this->error('No user or tasks selected');
            return;
        }
        
        // Get pending tasks for initial submission
       
        $pendingTasks = $this->selectedUserTasks->filter(function($task) {
            return $task->approvalstatus == 'pending' && $task->status != 'completed';
        });
        
        if ($pendingTasks->isEmpty()) {
            $this->error('No pending tasks found for bulk approval');
            return;
        }
        
        $taskIds = $pendingTasks->pluck('id')->toArray();
        
        // Bulk approve tasks
        $result = $this->repository->bulkapprovetasks([
            'task_ids' => $taskIds,
            'status' => $this->bulkApprovalStatus,
        ]);
        
        if ($result['status'] === 'success') {
            // Update calenderworkusertasks record
            $calendarweek = $this->gettasksbydepartment()['calendarweek'];
            if ($calendarweek) {
                // Map task approval status to calenderworkusertasks status
                $calenderStatus = $this->bulkApprovalStatus == 'Approved' ? 'approved' : 'rejected';
                $calendarResult = $this->calendarService->updatecalenderworkusertask(
                    $calendarweek->id,
                    $this->selectedUser->id,
                    [
                        'status' => $calenderStatus,
                        'comment' => $this->bulkApprovalComment,
                    ]
                );
            }
            
            $this->success($result['message']);
            $this->closeBulkApprovalModal();
            // Refresh the tasks
            $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        } else {
            $this->error($result['message']);
        }
    }

    public function openCompletedBulkApprovalModal($userId = null) {
        // If modal is already open, use existing selectedUser, otherwise load it
        if (!$this->selectedUser || ($userId && $this->selectedUser->id != $userId)) {
            $users = $this->gettasksbydepartment()['users'];
            $this->selectedUser = $users->find($userId);
            $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        }
        
        // Refresh tasks to ensure we have latest data
        if ($this->selectedUser) {
            $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        }
        
        // Check if there are completed tasks needing approval
        $completedTasks = $this->selectedUserTasks->filter(function($task) {
            return $task->approvalstatus == 'pending' && $task->status == 'completed';
        });
        
        if ($completedTasks->isEmpty()) {
            $this->error('No completed tasks found for bulk approval');
            return;
        }
        
        $this->showCompletedBulkApprovalModal = true;
        $this->completedBulkApprovalComment = '';
        $this->completedBulkApprovalStatus = 'Approved';
    }

    public function closeCompletedBulkApprovalModal() {
        $this->showCompletedBulkApprovalModal = false;
        $this->completedBulkApprovalComment = '';
        $this->completedBulkApprovalStatus = 'Approved';
    }

    public function bulkApproveCompletedTasks() {
        $this->validate([
            'completedBulkApprovalComment' => 'required',
        ], [
            'completedBulkApprovalComment.required' => 'Please provide a comment for the approval decision.',
        ]);
        
        if (!$this->selectedUser || !$this->selectedUserTasks) {
            $this->error('No user or tasks selected');
            return;
        }
        
        // Get completed tasks needing approval
        $completedTasks = $this->selectedUserTasks->filter(function($task) {
            return $task->approvalstatus == 'pending' && $task->status == 'completed';
        });
        
        if ($completedTasks->isEmpty()) {
            $this->error('No completed tasks found for bulk approval');
            return;
        }
        
        $taskIds = $completedTasks->pluck('id')->toArray();
        
        // Bulk approve completed tasks
        $result = $this->repository->bulkapprovetasks([
            'task_ids' => $taskIds,
            'status' => $this->completedBulkApprovalStatus,
        ]);
        
        if ($result['status'] === 'success') {
            $this->success($result['message']);
            $this->closeCompletedBulkApprovalModal();
            // Refresh the tasks
            $this->selectedUserTasks = $this->getUserActualTasks($this->selectedUser);
        } else {
            $this->error($result['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.weekytasks',[
            'weeks'=>$this->getweeks(),
            'tasks'=>$this->gettasksbydepartment()
        ]);
    }
}
 