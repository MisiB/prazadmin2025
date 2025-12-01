<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\itaskInterface;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class _taskRepository implements itaskInterface
{
    /**
     * Create a new class instance.
     */
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
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

            return ['status' => 'success', 'message' => 'Task marked successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approvetask(array $data)
    {
        try {
            $task = $this->task->where('id', $data['id'])->first();
            if ($task->approvalstatus != 'pending') {
                return ['status' => 'error', 'message' => 'You are not authorized to approve this task'];
            }
            
            $task->approvalstatus = $data['status'];
            $task->approved_by = Auth::user()->id;
            $task->save();

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
            
            foreach ($tasks as $task) {
                $task->approvalstatus = $status;
                $task->approved_by = Auth::user()->id;
                $task->save();
            }

            return ['status' => 'success', 'message' => count($tasks) . ' task(s) ' . strtolower($status) . ' successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
