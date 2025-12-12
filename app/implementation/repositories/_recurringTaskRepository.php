<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\irecurringTaskInterface;
use App\Models\RecurringTask;
use Carbon\Carbon;

class _recurringTaskRepository implements irecurringTaskInterface
{
    protected $recurringTask;

    public function __construct(RecurringTask $recurringTask)
    {
        $this->recurringTask = $recurringTask;
    }

    public function getmyrecurringtasks($userId)
    {
        return $this->recurringTask->where('user_id', $userId)
            ->with('taskTemplate', 'individualworkplan')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getrecurringtask($id)
    {
        return $this->recurringTask->with('taskTemplate', 'individualworkplan')->find($id);
    }

    public function createrecurringtask(array $data)
    {
        try {
            // Calculate next_create_date based on frequency
            $nextCreateDate = $this->calculateNextCreateDate($data);

            $data['next_create_date'] = $nextCreateDate;
            $recurringTask = $this->recurringTask->create($data);

            return ['status' => 'success', 'message' => 'Recurring task created successfully', 'data' => $recurringTask];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updaterecurringtask($id, array $data)
    {
        try {
            $recurringTask = $this->recurringTask->find($id);

            if (! $recurringTask) {
                return ['status' => 'error', 'message' => 'Recurring task not found'];
            }

            // Recalculate next_create_date if frequency or dates changed
            if (isset($data['frequency']) || isset($data['start_date']) || isset($data['day_of_week']) || isset($data['day_of_month'])) {
                $data['next_create_date'] = $this->calculateNextCreateDate(array_merge($recurringTask->toArray(), $data));
            }

            $recurringTask->update($data);

            return ['status' => 'success', 'message' => 'Recurring task updated successfully', 'data' => $recurringTask];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleterecurringtask($id)
    {
        try {
            $recurringTask = $this->recurringTask->find($id);

            if (! $recurringTask) {
                return ['status' => 'error', 'message' => 'Recurring task not found'];
            }

            $recurringTask->delete();

            return ['status' => 'success', 'message' => 'Recurring task deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getactiverecurringtasks()
    {
        $today = Carbon::today();

        return $this->recurringTask->where('is_active', true)
            ->where('next_create_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->with('user', 'taskTemplate', 'individualworkplan')
            ->get();
    }

    public function updatelastcreateddate($id, $date)
    {
        $recurringTask = $this->recurringTask->find($id);
        if ($recurringTask) {
            $recurringTask->last_created_date = $date;
            $recurringTask->next_create_date = $this->calculateNextCreateDate($recurringTask->toArray(), $date);
            $recurringTask->save();
        }
    }

    /**
     * Calculate the next create date based on frequency
     */
    private function calculateNextCreateDate(array $data, $fromDate = null): string
    {
        $startDate = $fromDate ? Carbon::parse($fromDate) : Carbon::parse($data['start_date']);
        $frequency = $data['frequency'];

        switch ($frequency) {
            case 'daily':
                // Next weekday
                $nextDate = $startDate->copy()->addDay();
                while ($nextDate->isWeekend()) {
                    $nextDate->addDay();
                }
                break;

            case 'weekly':
                $dayOfWeek = $data['day_of_week'] ?? 1; // Default to Monday
                $targetDay = $this->getDayName($dayOfWeek);
                $nextDate = $startDate->copy()->next($targetDay);
                // If today is the target day, move to next week
                if ($nextDate->isSameDay($startDate)) {
                    $nextDate->addWeek();
                }
                break;

            case 'monthly':
                $dayOfMonth = $data['day_of_month'] ?? 1;
                $nextDate = $startDate->copy()->addMonth();
                // Adjust if day doesn't exist in month (e.g., Feb 31)
                $lastDayOfMonth = $nextDate->copy()->endOfMonth()->day;
                $dayOfMonth = min($dayOfMonth, $lastDayOfMonth);
                $nextDate->day($dayOfMonth);
                // Skip weekends for monthly too
                while ($nextDate->isWeekend()) {
                    $nextDate->addDay();
                }
                break;

            default:
                $nextDate = $startDate->copy()->addDay();
        }

        return $nextDate->format('Y-m-d');
    }

    /**
     * Get day name from day number (1=Monday, 5=Friday)
     */
    private function getDayName(int $dayOfWeek): string
    {
        $days = [1 => Carbon::MONDAY, 2 => Carbon::TUESDAY, 3 => Carbon::WEDNESDAY, 4 => Carbon::THURSDAY, 5 => Carbon::FRIDAY];

        return $days[$dayOfWeek] ?? Carbon::MONDAY;
    }
}
