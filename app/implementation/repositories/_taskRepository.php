<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\itaskInterface;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Interfaces\services\ileaverequestService;
use App\Interfaces\repositories\idepartmentInterface;
use App\Notifications\TaskSubmittedForApproval;
use App\Notifications\TaskCompletedForApproval;
use App\Notifications\TaskApproved;

class _taskRepository implements itaskInterface
{
    /**
     * Create a new class instance.
     */
    protected $task;
    protected $leaverequestService;
    protected $departmentRepository;

    public function __construct(Task $task, ileaverequestService $leaverequestService, idepartmentInterface $departmentRepository)
    {
        $this->task = $task;
        $this->leaverequestService = $leaverequestService;
        $this->departmentRepository = $departmentRepository;
    }

    public function getmytasks($year)
    {
        $tasks = $this->task->with('user', 'individualworkplan')->where('user_id', Auth::user()->id)->whereYear('created_at', $year)->get();

        return $tasks;
    }

    public function gettask($id)
    {
        $task = $this->task->with('user', 'individualworkplan')->find($id);

        return $task;
    }

    public function createtask($data)
    {
        try {
            // Check if user is currently on leave
            $leaveStatus = $this->leaverequestService->isactiveonleave($data['user_id']);
            if ($leaveStatus['status'] === true) {
                return ['status' => 'error', 'message' => 'You cannot add tasks while you are on leave'];
            }
            
            $uuid = Str::uuid();
            $this->task->create([
                'title' => $data['title'],
                'user_id' => $data['user_id'],
                'individualworkplan_id' => $data['individualworkplan_id'],
                'calendarday_id' => $data['calendarday_id'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'duration' => $data['duration'],
                'uom' => $data['uom'],
                'uuid' => $uuid,
            ]);

            return ['status' => 'success', 'message' => 'Task created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updatetask($id, $data)
    {
        try {
            $task = $this->task->where('id', $id)->first();
            
            // If task was previously rejected, reset approval status to pending
            $updateData = [
                'title' => $data['title'],
                'user_id' => $data['user_id'],
                'individualworkplan_id' => $data['individualworkplan_id'],
                'calendarday_id' => $data['calendarday_id'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'duration' => $data['duration'],
                'uom' => $data['uom'],
            ];
            
            // Reset approval status if task was rejected
            if ($task->approvalstatus == 'Rejected') {
                $updateData['approvalstatus'] = 'pending';
            }
            
            $this->task->where('id', $id)->update($updateData);

            return ['status' => 'success', 'message' => 'Task updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletetask($id)
    {
        try {
            $task = $this->task->where('id', $id)->first();
            
            if (!$task) {
                return ['status' => 'error', 'message' => 'Task not found'];
            }
            
            // Only allow deletion if task is pending or rejected
            if ($task->approvalstatus != 'pending' && $task->approvalstatus != 'Rejected') {
                return ['status' => 'error', 'message' => 'You can only delete tasks that are pending or rejected'];
            }
            
            $task->delete();

            return ['status' => 'success', 'message' => 'Task deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function marktask($id, $status)
    {
        try {
            $updateData = [
                'status' => $status,
                'user_id' => Auth::user()->id,
            ];
            
            // When task is marked as completed, reset approval status to pending for completion approval
            if ($status == 'completed') {
                $updateData['approvalstatus'] = 'pending';
            }
            
            $this->task->where('id', "$id")->update($updateData);

            // Send notification to supervisor when task is marked as completed
            if ($status == 'completed') {
                $task = $this->task->where('id', $id)->first();
                if ($task) {
                    $userDepartment = \App\Models\Departmentuser::where('user_id', $task->user_id)->first();
                    if ($userDepartment && $userDepartment->reportto) {
                        // Check if supervisor is on leave and get acting supervisor
                        $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($userDepartment->reportto);
                        $notifyUserId = ($supervisorLeaveStatus['status'] === true && isset($supervisorLeaveStatus['actinghodid'])) 
                            ? $supervisorLeaveStatus['actinghodid'] 
                            : $userDepartment->reportto;
                        
                        $supervisor = \App\Models\User::find($notifyUserId);
                        if ($supervisor) {
                            $supervisor->notify(new TaskCompletedForApproval($this, $id));
                        }
                    }
                }
            }

            return ['status' => 'success', 'message' => 'Task marked successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approvetask(array $data)
    {
        try {
            $task = $this->task->where('id', $data['id'])->first();
            
            if (!$task) {
                return ['status' => 'error', 'message' => 'Task not found'];
            }
            
            if ($task->approvalstatus != 'pending') {
                return ['status' => 'error', 'message' => 'You are not authorized to approve this task'];
            }
            
            // Check if the task owner reports to the current approver
            $taskOwnerDepartment = \App\Models\Departmentuser::where('user_id', $task->user_id)->first();
            if (!$taskOwnerDepartment) {
                return ['status' => 'error', 'message' => 'Task owner department information not found'];
            }
            
            $supervisorId = $taskOwnerDepartment->reportto;
            $canApprove = false;
            
            // Check if current user is the direct supervisor
            if ($supervisorId == Auth::user()->id) {
                $canApprove = true;
            } else {
                // Check if supervisor is on leave and current user is in same department
                $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);
                if ($supervisorLeaveStatus['status'] === true) {
                    // Supervisor is on leave, check if current user is in same department
                    $currentUserDepartment = \App\Models\Departmentuser::where('user_id', Auth::user()->id)->first();
                    if ($currentUserDepartment && $currentUserDepartment->department_id == $taskOwnerDepartment->department_id) {
                        // Check if current user is a supervisor (has subordinates) in the same department
                        $hasSubordinates = \App\Models\Departmentuser::where('reportto', Auth::user()->id)
                            ->where('department_id', $taskOwnerDepartment->department_id)
                            ->exists();
                        if ($hasSubordinates) {
                            $canApprove = true;
                        }
                    }
                }
            }
            
            if (!$canApprove) {
                return ['status' => 'error', 'message' => 'You can only approve tasks for members you directly supervise, or for members whose supervisor is on leave'];
            }
            
            $task->approvalstatus = $data['status'];
            $task->approved_by = Auth::user()->id;
            $task->save();

            // Send notification to task owner
            $taskOwner = \App\Models\User::find($task->user_id);
            if ($taskOwner) {
                $taskOwner->notify(new TaskApproved($this, $task->id, $data['status']));
            }

            return ['status' => 'success', 'message' => 'Action successfully completed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function bulkapprovetasks(array $data)
    {
        try {
            $taskIds = $data['task_ids'];
            $status = $data['status'];
            
            $tasks = $this->task->whereIn('id', $taskIds)
                ->where('approvalstatus', 'pending')
                ->get();
            
            if ($tasks->isEmpty()) {
                return ['status' => 'error', 'message' => 'No pending tasks found to approve'];
            }
            
            // Filter tasks to only include those the user can approve
            $approvedTasks = collect();
            foreach ($tasks as $task) {
                $taskOwnerDepartment = \App\Models\Departmentuser::where('user_id', $task->user_id)->first();
                if (!$taskOwnerDepartment) {
                    continue;
                }
                
                $supervisorId = $taskOwnerDepartment->reportto;
                $canApprove = false;
                
                // Check if current user is the direct supervisor
                if ($supervisorId == Auth::user()->id) {
                    $canApprove = true;
                } else {
                    // Check if supervisor is on leave and current user is in same department
                    $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);
                    if ($supervisorLeaveStatus['status'] === true) {
                        // Supervisor is on leave, check if current user is in same department
                        $currentUserDepartment = \App\Models\Departmentuser::where('user_id', Auth::user()->id)->first();
                        if ($currentUserDepartment && $currentUserDepartment->department_id == $taskOwnerDepartment->department_id) {
                            // Check if current user is a supervisor (has subordinates) in the same department
                            $hasSubordinates = \App\Models\Departmentuser::where('reportto', Auth::user()->id)
                                ->where('department_id', $taskOwnerDepartment->department_id)
                                ->exists();
                            if ($hasSubordinates) {
                                $canApprove = true;
                            }
                        }
                    }
                }
                
                if ($canApprove) {
                    $approvedTasks->push($task);
                }
            }
            
            if ($approvedTasks->isEmpty()) {
                return ['status' => 'error', 'message' => 'You can only approve tasks for members you directly supervise, or for members whose supervisor is on leave'];
            }
            
            // Group tasks by user_id to send one notification per user
            $tasksByUser = $approvedTasks->groupBy('user_id');
            
            foreach ($approvedTasks as $task) {
                $task->approvalstatus = $status;
                $task->approved_by = Auth::user()->id;
                $task->save();
            }

            // Send notifications to task owners (one per user)
            foreach ($tasksByUser as $userId => $userTasks) {
                $taskOwner = \App\Models\User::find($userId);
                if ($taskOwner && $userTasks->isNotEmpty()) {
                    // Send notification with first task ID (you could modify to send all task IDs)
                    $taskOwner->notify(new TaskApproved($this, $userTasks->first()->id, $status));
                }
            }

            return ['status' => 'success', 'message' => count($approvedTasks) . ' task(s) ' . strtolower($status) . ' successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
