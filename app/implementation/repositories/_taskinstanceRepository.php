<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\itaskinstanceInterface;
use App\Models\Taskinstance;

class _taskinstanceRepository implements itaskinstanceInterface
{
    protected $taskinstance;

    public function __construct(Taskinstance $taskinstance)
    {
        $this->taskinstance = $taskinstance;
    }

    public function getall()
    {
        return $this->taskinstance->with('task')->get();
    }

    public function getbyid($id)
    {
        return $this->taskinstance->with('task')->find($id);
    }

    public function getbytaskid($taskId)
    {
        return $this->taskinstance->with('task')->where('task_id', $taskId)->get();
    }

    public function getinstancesbydate($date)
    {
        return $this->taskinstance->with('task')
            ->whereDate('date', $date)
            ->get();
    }

    public function getinstancesbydateanduser($date, $userId)
    {
        return $this->taskinstance->with('task')
            ->whereDate('date', $date)
            ->whereHas('task', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();
    }

    public function getactiveinstancebytaskid($taskId)
    {
        // Use fresh query to avoid caching
        return Taskinstance::where('task_id', $taskId)
            ->where('status', 'ongoing')
            ->orderBy('date', 'desc')
            ->first();
    }

    public function getinstancesbytaskids(array $taskIds, $filters = [])
    {
        $query = $this->taskinstance->whereIn('task_id', $taskIds);

        if (isset($filters['orderBy'])) {
            $query->orderBy($filters['orderBy']['column'], $filters['orderBy']['direction'] ?? 'asc');
        }

        return $query->get();
    }

    public function create(array $data)
    {
        try {
            $taskinstance = $this->taskinstance->create([
                'task_id' => $data['task_id'],
                'date' => $data['date'],
                'planned_hours' => $data['planned_hours'] ?? 0,
                'worked_hours' => $data['worked_hours'] ?? 0,
                'status' => $data['status'] ?? 'ongoing',
            ]);

            return ['status' => 'success', 'message' => 'Task instance created successfully', 'data' => $taskinstance];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update($id, array $data)
    {
        try {
            // Use fresh query to avoid any caching issues
            $taskinstance = Taskinstance::find($id);

            if (! $taskinstance) {
                return ['status' => 'error', 'message' => 'Task instance not found'];
            }

            $taskinstance->update($data);

            // Refresh the model to get the updated data from database
            $taskinstance->refresh();

            return ['status' => 'success', 'message' => 'Task instance updated successfully', 'data' => $taskinstance];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $taskinstance = $this->taskinstance->find($id);

            if (! $taskinstance) {
                return ['status' => 'error', 'message' => 'Task instance not found'];
            }

            $taskinstance->delete();

            return ['status' => 'success', 'message' => 'Task instance deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
