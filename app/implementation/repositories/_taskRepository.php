<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\ileaverequestService;
use App\Models\Task;
use App\Notifications\TaskApproved;
use App\Notifications\TaskCompletedForApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            $task = $this->task->create([
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

            // Auto-create TaskInstance for today with planned hours from duration
            \App\Models\Taskinstance::create([
                'task_id' => $task->id,
                'date' => now()->format('Y-m-d'),
                'planned_hours' => $data['duration'] ?? 0,
                'worked_hours' => 0,
                'status' => 'ongoing',
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

            if (! $task) {
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

    public function marktask($id, $status, $evidencePath = null, $originalName = null)
    {
        try {
            $task = $this->task->with('calendarday')->find($id);

            if (! $task) {
                return ['status' => 'error', 'message' => 'Task not found'];
            }

            // When marking as completed, check if hours have been logged
            if ($status == 'completed') {
                $totalWorkedHours = \App\Models\Taskinstance::where('task_id', $id)->sum('worked_hours');
                if ($totalWorkedHours <= 0) {
                    return ['status' => 'error', 'message' => 'You must log hours before marking this task as completed'];
                }
            }

            $updateData = [
                'status' => $status,
                'user_id' => Auth::user()->id,
            ];

            // When task is marked as completed, reset approval status to pending for completion approval
            if ($status == 'completed') {
                $updateData['approvalstatus'] = 'pending';

                // Add evidence if provided
                if ($evidencePath) {
                    $updateData['evidence_path'] = $evidencePath;
                    $updateData['evidence_original_name'] = $originalName;
                }
            }

            $this->task->where('id', "$id")->update($updateData);

            // When marking as ongoing, create a task instance if one doesn't exist
            if ($status == 'ongoing') {
                // Check if there's already an active instance
                $existingInstance = \App\Models\Taskinstance::where('task_id', $id)
                    ->where('status', 'ongoing')
                    ->first();

                if (! $existingInstance) {
                    // Get the date from the calendar day or use today's date
                    $date = $task->calendarday ? $task->calendarday->maindate : now()->format('Y-m-d');
                    
                    // Create task instance with 0 worked hours
                    \App\Models\Taskinstance::create([
                        'task_id' => $id,
                        'date' => $date,
                        'planned_hours' => $task->duration ?? 0,
                        'worked_hours' => 0,
                        'status' => 'ongoing',
                    ]);
                }
            }

            // Send notification to supervisor when task is marked as completed
            if ($status == 'completed') {
                $task = $this->task->where('id', $id)->first();
                if ($task) {
                    $notifyUserId = $this->getNotificationRecipient($task->user_id);
                    if ($notifyUserId) {
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

            if (! $task) {
                return ['status' => 'error', 'message' => 'Task not found'];
            }

            if ($task->approvalstatus != 'pending') {
                return ['status' => 'error', 'message' => 'You are not authorized to approve this task'];
            }

            // Check if the task owner reports to the current approver
            $taskOwnerDepartment = \App\Models\Departmentuser::where('user_id', $task->user_id)->first();
            if (! $taskOwnerDepartment) {
                return ['status' => 'error', 'message' => 'Task owner department information not found'];
            }

            $supervisorId = $taskOwnerDepartment->reportto;
            $canApprove = false;

            // Check if current user is the direct supervisor
            if ($supervisorId == Auth::user()->id) {
                $canApprove = true;
            } else {
                // Check if supervisor is on leave and get acting supervisor
                $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);
                if ($supervisorLeaveStatus['status'] === true) {
                    // Check if current user is the acting supervisor assigned
                    if (isset($supervisorLeaveStatus['actinghodid']) && $supervisorLeaveStatus['actinghodid'] == Auth::user()->id) {
                        $canApprove = true;
                    } else {
                        // Supervisor is on leave, check if current user is in same department and is a supervisor
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
            }

            // Fallback: If all supervisors are unavailable, allow HOD or Acting HOD to approve
            if (! $canApprove) {
                $currentUserDepartment = \App\Models\Departmentuser::where('user_id', Auth::user()->id)->first();
                if ($currentUserDepartment && $currentUserDepartment->department_id == $taskOwnerDepartment->department_id) {
                    // Check if current user is HOD (isprimary = true) or has Acting HOD role
                    $isHOD = $currentUserDepartment->isprimary == true;
                    $hasActingHODRole = Auth::user()->hasRole('Acting HOD');

                    if ($isHOD || $hasActingHODRole) {
                        // Check if all supervisors in the chain are unavailable
                        $supervisorStatus = $this->areAllSupervisorsUnavailable($supervisorId, $taskOwnerDepartment->department_id);
                        if ($supervisorStatus['unavailable']) {
                            $canApprove = true;
                        }
                    }

                    // Also allow HOD's acting member to approve when HOD is on leave
                    if (! $canApprove) {
                        $supervisorStatus = $this->areAllSupervisorsUnavailable($supervisorId, $taskOwnerDepartment->department_id);
                        if ($supervisorStatus['unavailable'] && isset($supervisorStatus['actinghodid']) && $supervisorStatus['actinghodid'] == Auth::user()->id) {
                            $canApprove = true;
                        }
                    }
                }
            }

            if (! $canApprove) {
                return ['status' => 'error', 'message' => 'You can only approve tasks for members you directly supervise, or when all supervisors are unavailable and you are the HOD/Acting HOD'];
            }

            $task->approvalstatus = $data['status'];
            $task->approved_by = Auth::user()->id;
            if (isset($data['comment']) && ! empty($data['comment'])) {
                $task->approval_comment = $data['comment'];
            }
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
                if (! $taskOwnerDepartment) {
                    continue;
                }

                $supervisorId = $taskOwnerDepartment->reportto;
                $canApprove = false;

                // Check if current user is the direct supervisor
                if ($supervisorId == Auth::user()->id) {
                    $canApprove = true;
                } else {
                    // Check if supervisor is on leave and get acting supervisor
                    $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);
                    if ($supervisorLeaveStatus['status'] === true) {
                        // Check if current user is the acting supervisor assigned by HOD
                        if (isset($supervisorLeaveStatus['actinghod_id']) && $supervisorLeaveStatus['actinghod_id'] == Auth::user()->id) {
                            $canApprove = true;
                        } else {
                            // Supervisor is on leave, check if current user is in same department and is a supervisor
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
                }

                // Fallback: If all supervisors are unavailable, allow HOD or Acting HOD to approve
                if (! $canApprove) {
                    $currentUserDepartment = \App\Models\Departmentuser::where('user_id', Auth::user()->id)->first();
                    if ($currentUserDepartment && $currentUserDepartment->department_id == $taskOwnerDepartment->department_id) {
                        // Check if current user is HOD (isprimary = true) or has Acting HOD role
                        $isHOD = $currentUserDepartment->isprimary == true;
                        $hasActingHODRole = Auth::user()->hasRole('Acting HOD');

                        if ($isHOD || $hasActingHODRole) {
                            // Check if all supervisors in the chain are unavailable
                            $supervisorStatus = $this->areAllSupervisorsUnavailable($supervisorId, $taskOwnerDepartment->department_id);
                            if ($supervisorStatus['unavailable']) {
                                $canApprove = true;
                            }
                        }

                        // Also allow HOD's acting member to approve when HOD is on leave
                        if (! $canApprove) {
                            $supervisorStatus = $this->areAllSupervisorsUnavailable($supervisorId, $taskOwnerDepartment->department_id);
                            if ($supervisorStatus['unavailable'] && isset($supervisorStatus['actinghodid']) && $supervisorStatus['actinghodid'] == Auth::user()->id) {
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
                return ['status' => 'error', 'message' => 'You can only approve tasks for members you directly supervise, or when all supervisors are unavailable and you are the HOD/Acting HOD'];
            }

            // Group tasks by user_id to send one notification per user
            $tasksByUser = $approvedTasks->groupBy('user_id');

            $comment = $data['comment'] ?? null;

            foreach ($approvedTasks as $task) {
                $task->approvalstatus = $status;
                $task->approved_by = Auth::user()->id;
                if (! empty($comment)) {
                    $task->approval_comment = $comment;
                }
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

            return ['status' => 'success', 'message' => count($approvedTasks).' task(s) '.strtolower($status).' successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if all supervisors in the chain are unavailable (on leave or don't exist)
     * Returns array with status and actinghodid if HOD assigned an acting member
     */
    private function areAllSupervisorsUnavailable($supervisorId, $departmentId): array
    {
        if (! $supervisorId) {
            return ['unavailable' => true, 'actinghodid' => null];
        }

        // Check if supervisor is on leave
        $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);
        if ($supervisorLeaveStatus['status'] === true) {
            // Supervisor is on leave, check if there's an acting supervisor assigned
            if (isset($supervisorLeaveStatus['actinghodid']) && $supervisorLeaveStatus['actinghodid']) {
                // There's an acting supervisor, so supervisors are not all unavailable
                return ['unavailable' => false, 'actinghodid' => null];
            }

            // Supervisor is on leave but no acting supervisor assigned
            // Check if there are other supervisors in the department who can approve
            $otherSupervisors = \App\Models\Departmentuser::where('department_id', $departmentId)
                ->where('user_id', '!=', $supervisorId)
                ->whereNotNull('reportto')
                ->get()
                ->filter(function ($deptUser) {
                    // Check if this user is a supervisor (has subordinates)
                    return \App\Models\Departmentuser::where('reportto', $deptUser->user_id)
                        ->where('department_id', $deptUser->department_id)
                        ->exists();
                });

            // Check if other supervisors are available (not on leave)
            foreach ($otherSupervisors as $otherSupervisor) {
                $otherLeaveStatus = $this->leaverequestService->isactiveonleave($otherSupervisor->user_id);
                if ($otherLeaveStatus['status'] !== true) {
                    // Found an available supervisor
                    return ['unavailable' => false, 'actinghodid' => null];
                }
            }

            // All regular supervisors unavailable - check if HOD is available
            $hod = \App\Models\Departmentuser::where('department_id', $departmentId)
                ->where('isprimary', true)
                ->first();

            if ($hod) {
                $hodLeaveStatus = $this->leaverequestService->isactiveonleave($hod->user_id);
                if ($hodLeaveStatus['status'] !== true) {
                    // HOD is available
                    return ['unavailable' => false, 'actinghodid' => null];
                } else {
                    // HOD is on leave - check if HOD assigned an acting member
                    if (isset($hodLeaveStatus['actinghodid']) && $hodLeaveStatus['actinghodid']) {
                        return ['unavailable' => true, 'actinghodid' => $hodLeaveStatus['actinghodid']];
                    }
                }
            }

            // No one available
            return ['unavailable' => true, 'actinghodid' => null];
        }

        // Supervisor is not on leave, so they are available
        return ['unavailable' => false, 'actinghodid' => null];
    }

    /**
     * Get the correct supervisor to notify based on leave status chain
     * Priority: Direct Supervisor → Supervisor's Acting → HOD → HOD's Acting
     */
    private function getNotificationRecipient($userId): ?string
    {
        $userDepartment = \App\Models\Departmentuser::where('user_id', $userId)->first();
        if (! $userDepartment || ! $userDepartment->reportto) {
            return null;
        }

        $supervisorId = $userDepartment->reportto;

        // Check if direct supervisor is on leave
        $supervisorLeaveStatus = $this->leaverequestService->isactiveonleave($supervisorId);
        if ($supervisorLeaveStatus['status'] !== true) {
            // Supervisor is available - send to them
            return $supervisorId;
        }

        // Supervisor is on leave - check if they assigned an acting person
        if (isset($supervisorLeaveStatus['actinghodid']) && $supervisorLeaveStatus['actinghodid']) {
            return $supervisorLeaveStatus['actinghodid'];
        }

        // No acting assigned by supervisor - check HOD
        $hod = \App\Models\Departmentuser::where('department_id', $userDepartment->department_id)
            ->where('isprimary', true)
            ->first();

        if ($hod) {
            $hodLeaveStatus = $this->leaverequestService->isactiveonleave($hod->user_id);
            if ($hodLeaveStatus['status'] !== true) {
                // HOD is available
                return $hod->user_id;
            } else {
                // HOD is on leave - use their acting member
                if (isset($hodLeaveStatus['actinghodid']) && $hodLeaveStatus['actinghodid']) {
                    return $hodLeaveStatus['actinghodid'];
                }
            }
        }

        // Fallback to direct supervisor (even if on leave, still notify)
        return $supervisorId;
    }

    public function gettasksbyuseranddaterange($userId, $startDate, $endDate)
    {
        return $this->task->with(['calendarday', 'user', 'individualworkplan'])
            ->where('user_id', $userId)
            ->whereHas('calendarday', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('maindate', [$startDate, $endDate]);
            })
            ->get();
    }

    public function getpendingorongoingtasksbyuseranddaterange($userId, $startDate, $endDate)
    {
        return $this->task->with(['calendarday', 'user', 'individualworkplan'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'ongoing'])
            ->whereHas('calendarday', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('maindate', [$startDate, $endDate]);
            })
            ->get();
    }

    public function gettasksbyuseridsanddaterange($userIds, $startDate, $endDate)
    {
        return $this->task->with(['calendarday', 'user', 'individualworkplan'])
            ->whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function gettasksbyuserids($userIds, $filters = [])
    {
        $withRelations = ['calendarday', 'user', 'individualworkplan'];
        if (isset($filters['with'])) {
            $withRelations = array_merge($withRelations, $filters['with']);
        }

        $query = $this->task->with($withRelations)
            ->whereIn('user_id', $userIds);

        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (isset($filters['approvalstatus'])) {
            if (is_array($filters['approvalstatus'])) {
                $query->whereIn('approvalstatus', $filters['approvalstatus']);
            } else {
                $query->where('approvalstatus', $filters['approvalstatus']);
            }
        }

        if (isset($filters['whereNotNull'])) {
            foreach ($filters['whereNotNull'] as $field) {
                $query->whereNotNull($field);
            }
        }

        if (isset($filters['whereNull'])) {
            foreach ($filters['whereNull'] as $field) {
                $query->whereNull($field);
            }
        }

        if (isset($filters['orderBy'])) {
            $query->orderBy($filters['orderBy']['column'], $filters['orderBy']['direction'] ?? 'asc');
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }

    public function getlinkedtaskscountbyuserids($userIds)
    {
        return $this->task->whereIn('user_id', $userIds)
            ->whereNotNull('individualworkplan_id')
            ->count();
    }

    public function gettotaltaskscountbyuserids($userIds)
    {
        return $this->task->whereIn('user_id', $userIds)->count();
    }

    public function gettaskidsbyuseridsanddaterange($userIds, $startDate, $endDate)
    {
        return $this->task->whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('id');
    }

    public function gettasksbyuseridanddaterange($userId, $startDate, $endDate)
    {
        return $this->task->with(['calendarday', 'user', 'individualworkplan'])
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function gettaskswithcalendardaybyuseridsanddaterange($userIds, $startDate, $endDate)
    {
        return $this->task->with('calendarday')
            ->whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function gettasksbyuseridsandcalendarweek($userIds, $calendarweekId)
    {
        return $this->task->with('calendarday')
            ->whereIn('user_id', $userIds)
            ->whereHas('calendarday', function ($query) use ($calendarweekId) {
                $query->where('calendarweek_id', $calendarweekId);
            })
            ->get();
    }
}
