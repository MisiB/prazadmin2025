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
            $this->task->where('id', $id)->update([
                'title' => $data['title'],
                'user_id' => $data['user_id'],
                'individualworkplan_id' => $data['individualworkplan_id'],
                'calendarday_id' => $data['calendarday_id'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'duration' => $data['duration'],
                'uom' => $data['uom'],
            ]);

            return ['status' => 'success', 'message' => 'Task updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletetask($id)
    {
        try {
            $this->task->where('id', "$id")->delete();

            return ['status' => 'success', 'message' => 'Task deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function marktask($id, $status)
    {
        try {
            $this->task->where('id', "$id")->update([
                'status' => $status,
                'user_id' => Auth::user()->id,
            ]);

            return ['status' => 'success', 'message' => 'Task marked successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approvetask(array $data)
    {
        try {
            $task = $this->task->where('id', $data['id'])->first();
            if ($task->status != 'Pending') {
                return ['status' => 'error', 'message' => 'You are not authorized to approve this task'];
            }
            if ($data['status'] != 'Approved') {
                $task->comment = json_encode($data['comments']);
            } else {
                $task->approval_status = $data['status'];
                $task->approved_by = Auth::user()->id;
            }
            $task->save();

            return ['status' => 'success', 'message' => 'Action successfully completed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
