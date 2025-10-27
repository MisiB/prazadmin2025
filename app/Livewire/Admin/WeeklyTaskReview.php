<?php

namespace App\Livewire\Admin;

use App\Models\WeeklyTaskReview as WeeklyTaskReviewModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class WeeklyTaskReview extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $weekStartDate;

    public $weekEndDate;

    public $tasks = [];

    public $taskReviews = [];

    public $overallComment = '';

    public $totalTasks = 0;

    public $completedTasks = 0;

    public $incompleteTasks = 0;

    public $completionRate = 0;

    public $totalHoursPlanned = 0;

    public $totalHoursCompleted = 0;

    public $existingReview = null;

    public $isSubmitted = false;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Weekly Task Review'],
        ];

        // Load last week's tasks for review
        $this->loadLastWeekTasks();
    }

    public function loadLastWeekTasks()
    {
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $this->weekStartDate = $lastWeekStart->format('Y-m-d');
        $this->weekEndDate = $lastWeekEnd->format('Y-m-d');

        // Check if review already exists
        $this->existingReview = WeeklyTaskReviewModel::where('user_id', Auth::id())
            ->where('week_start_date', $this->weekStartDate)
            ->first();

        if ($this->existingReview) {
            $this->loadExistingReview();
        } else {
            $this->loadTasksFromCalendar();
        }
    }

    public function loadExistingReview()
    {
        $this->taskReviews = $this->existingReview->task_reviews ?? [];
        $this->overallComment = $this->existingReview->overall_comment ?? '';
        $this->completedTasks = $this->existingReview->completed_tasks;
        $this->incompleteTasks = $this->existingReview->incomplete_tasks;
        $this->totalTasks = $this->existingReview->total_tasks;
        $this->completionRate = $this->existingReview->completion_rate;
        $this->totalHoursPlanned = $this->existingReview->total_hours_planned;
        $this->totalHoursCompleted = $this->existingReview->total_hours_completed;
        $this->isSubmitted = $this->existingReview->is_submitted;

        // Reconstruct tasks array from review data
        foreach ($this->taskReviews as $review) {
            $this->tasks[] = (object) [
                'name' => $review['task_name'],
                'hours' => $review['hours'],
                'day' => $review['day'],
                'status' => $review['original_status'],
            ];
        }
    }

    public function loadTasksFromCalendar()
    {
        $this->tasks = [];

        // Get tasks from calendar (assuming there's a calendar system)
        if (class_exists(\App\Models\Weekdaycalendar::class)) {
            $calendar = \App\Models\Weekdaycalendar::where('user_id', Auth::id())
                ->whereBetween('date', [$this->weekStartDate, $this->weekEndDate])
                ->first();

            if ($calendar) {
                $days = [
                    'monday' => $calendar->monday,
                    'tuesday' => $calendar->tuesday,
                    'wednesday' => $calendar->wednesday,
                    'thursday' => $calendar->thursday,
                    'friday' => $calendar->friday,
                ];

                foreach ($days as $dayName => $day) {
                    if ($day && isset($day->tasks)) {
                        foreach ($day->tasks as $task) {
                            $this->tasks[] = (object) [
                                'name' => $task->name,
                                'hours' => $task->hours ?? 0,
                                'day' => ucfirst($dayName),
                                'status' => $task->status ?? 'pending',
                            ];

                            // Initialize review for this task
                            $this->taskReviews[] = [
                                'task_name' => $task->name,
                                'hours' => $task->hours ?? 0,
                                'day' => ucfirst($dayName),
                                'original_status' => $task->status ?? 'pending',
                                'was_completed' => false,
                                'completion_comment' => '',
                            ];
                        }
                    }
                }
            }
        }

        $this->calculateTotals();
    }

    public function toggleTaskCompletion($index)
    {
        if (! $this->isSubmitted) {
            $this->taskReviews[$index]['was_completed'] = ! $this->taskReviews[$index]['was_completed'];
            $this->calculateTotals();
        }
    }

    public function updateTaskComment($index, $comment)
    {
        if (! $this->isSubmitted) {
            $this->taskReviews[$index]['completion_comment'] = $comment;
        }
    }

    public function calculateTotals()
    {
        $this->totalTasks = count($this->taskReviews);
        $this->completedTasks = collect($this->taskReviews)->where('was_completed', true)->count();
        $this->incompleteTasks = $this->totalTasks - $this->completedTasks;

        $this->completionRate = $this->totalTasks > 0
            ? round(($this->completedTasks / $this->totalTasks) * 100, 2)
            : 0;

        $this->totalHoursPlanned = collect($this->taskReviews)->sum('hours');
        $this->totalHoursCompleted = collect($this->taskReviews)
            ->where('was_completed', true)
            ->sum('hours');
    }

    public function submitReview()
    {
        $this->validate([
            'overallComment' => 'nullable|string|max:1000',
        ]);

        $this->calculateTotals();

        $data = [
            'user_id' => Auth::id(),
            'week_start_date' => $this->weekStartDate,
            'week_end_date' => $this->weekEndDate,
            'total_tasks' => $this->totalTasks,
            'completed_tasks' => $this->completedTasks,
            'incomplete_tasks' => $this->incompleteTasks,
            'completion_rate' => $this->completionRate,
            'total_hours_planned' => $this->totalHoursPlanned,
            'total_hours_completed' => $this->totalHoursCompleted,
            'task_reviews' => $this->taskReviews,
            'overall_comment' => $this->overallComment,
            'reviewed_at' => now(),
            'is_submitted' => true,
        ];

        if ($this->existingReview) {
            $this->existingReview->update($data);
            $this->success('Weekly review updated successfully!');
        } else {
            WeeklyTaskReviewModel::create($data);
            $this->success('Weekly review submitted successfully!');
        }

        $this->isSubmitted = true;
    }

    public function saveDraft()
    {
        $this->calculateTotals();

        $data = [
            'user_id' => Auth::id(),
            'week_start_date' => $this->weekStartDate,
            'week_end_date' => $this->weekEndDate,
            'total_tasks' => $this->totalTasks,
            'completed_tasks' => $this->completedTasks,
            'incomplete_tasks' => $this->incompleteTasks,
            'completion_rate' => $this->completionRate,
            'total_hours_planned' => $this->totalHoursPlanned,
            'total_hours_completed' => $this->totalHoursCompleted,
            'task_reviews' => $this->taskReviews,
            'overall_comment' => $this->overallComment,
            'is_submitted' => false,
        ];

        if ($this->existingReview) {
            $this->existingReview->update($data);
        } else {
            $this->existingReview = WeeklyTaskReviewModel::create($data);
        }

        $this->success('Draft saved successfully!');
    }

    public function render()
    {
        return view('livewire.admin.weekly-task-review');
    }
}
