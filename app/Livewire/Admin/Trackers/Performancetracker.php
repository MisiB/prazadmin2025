<?php

namespace App\Livewire\Admin\Trackers;

use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\services\ICalendarService;
use App\Models\WeeklyTaskReview;
use Carbon\Carbon;
use Livewire\Component;

class Performancetracker extends Component
{
    public $breadcrumbs = [];

    protected $calendarService;

    protected $repository;

    protected $workplanrepository;

    public $currentWeek = null;

    public $currentWeekId;

    public $week;

    public $year;

    public $startDate;

    public $endDate;

    public $showModal = false;

    public $selectedUser = null;

    public $tasks;

    public $myChart = [];

    public $selectedUserTasks = [];

    public function boot(ICalendarService $calendarService, itaskInterface $repository, individualworkplanInterface $workplanrepository)
    {
        $this->calendarService = $calendarService;
        $this->repository = $repository;
        $this->workplanrepository = $workplanrepository;
    }

    public function mount()
    {
        $this->year = Carbon::now()->year;
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        $this->tasks = [];
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Weekly departmental tasks'],
        ];
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        $this->getweeks();
        $this->gettasksbydepartment();

    }

    public function getweeks()
    {
        $data = $this->calendarService->getweeks($this->year);
        $this->currentWeek = $data->where('start_date', '>=', $this->startDate)->where('end_date', '<=', $this->endDate)->first();

        return $data;
    }

    public function updatedCurrentWeekId()
    {
        if ($this->currentWeekId) {
            $data = $this->calendarService->gettasksbycalenderweek($this->currentWeekId);
            $this->tasks = $data;
            $this->getMyChart();
        }
    }

    public function gettasksbydepartment()
    {

        if ($this->currentWeek) {
            $this->currentWeekId = $this->currentWeek->id;
            if ($this->currentWeekId) {
                $data = $this->calendarService->gettasksbycalenderweek($this->currentWeekId);

                $this->tasks = $data;
                $this->getMyChart();
            }
        }

        return [];
    }

    public function getTotalTasksCount($users)
    {
        return $users->sum(function ($user) {
            // Use week_tasks if available, otherwise fall back to the nested relationship
            if (isset($user->week_tasks)) {
                return $user->week_tasks->count();
            }

            return $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                    return $calendarday->tasks->count();
                });
            });
        });
    }

    public function getUsersWithTasksCount($users)
    {
        return $users->filter(function ($user) {
            $actualTasksCount = 0;

            // Use week_tasks if available
            if (isset($user->week_tasks)) {
                $actualTasksCount = $user->week_tasks->count();
            } else {
                $actualTasksCount = $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
                    return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                        return $calendarday->tasks->count();
                    });
                });
            }

            return $actualTasksCount > 0;
        })->count();
    }

    public function getUserTaskCount($user)
    {
        // Use week_tasks if available
        if (isset($user->week_tasks)) {
            return $user->week_tasks->count();
        }

        return $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
            return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                return $calendarday->tasks->count();
            });
        });
    }

    public function getUserActualTasks($user)
    {
        // Use week_tasks if available
        if (isset($user->week_tasks)) {
            return $user->week_tasks;
        }

        $tasks = collect();
        $user->calenderworkusertasks->each(function ($calenderworkusertask) use ($tasks) {
            $calenderworkusertask->calendarweek->calendardays->each(function ($calendarday) use ($tasks) {
                $tasks->push(...$calendarday->tasks);
            });
        });

        return $tasks;
    }

    public function getLinkedTasksCount($users)
    {
        return $users->sum(function ($user) {
            if (isset($user->week_tasks)) {
                return $user->week_tasks->whereNotNull('individualworkplan_id')->count();
            }

            return $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                    return $calendarday->tasks->whereNotNull('individualworkplan_id')->count();
                });
            });
        });
    }

    public function getUnlinkedTasksCount($users)
    {
        return $users->sum(function ($user) {
            if (isset($user->week_tasks)) {
                return $user->week_tasks->whereNull('individualworkplan_id')->count();
            }

            return $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
                return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                    return $calendarday->tasks->whereNull('individualworkplan_id')->count();
                });
            });
        });
    }

    public function getLinkedTasksPercentage($users)
    {
        $totalTasks = $this->getTotalTasksCount($users);
        if ($totalTasks == 0) {
            return 0;
        }

        $linkedTasks = $this->getLinkedTasksCount($users);

        return round(($linkedTasks / $totalTasks) * 100, 1);
    }

    public function getUserLinkedTasksCount($user)
    {
        if (isset($user->week_tasks)) {
            return $user->week_tasks->whereNotNull('individualworkplan_id')->count();
        }

        return $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
            return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                return $calendarday->tasks->whereNotNull('individualworkplan_id')->count();
            });
        });
    }

    public function getUserUnlinkedTasksCount($user)
    {
        if (isset($user->week_tasks)) {
            return $user->week_tasks->whereNull('individualworkplan_id')->count();
        }

        return $user->calenderworkusertasks->sum(function ($calenderworkusertask) {
            return $calenderworkusertask->calendarweek->calendardays->sum(function ($calendarday) {
                return $calendarday->tasks->whereNull('individualworkplan_id')->count();
            });
        });
    }

    public function openTaskModal($userId)
    {
        $users = $this->tasks['users'];
        $this->selectedUser = $users->find($userId);

        // Directly fetch tasks for this user in the current week
        if ($this->currentWeekId && $this->selectedUser) {
            $tasks = \App\Models\Task::whereHas('calendarday', function ($query) {
                $query->where('calendarweek_id', $this->currentWeekId);
            })->where('user_id', $this->selectedUser->id)->get();

            $this->selectedUserTasks = $tasks;
        } else {
            $this->selectedUserTasks = collect();
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedUser = null;
        $this->selectedUserTasks = [];
    }

    public function getUsersByDepartment($users)
    {
        return $users->groupBy(function ($user) {
            return $user->department ? $user->department->department->name : 'No Department';
        });
    }

    public function getDepartmentStats($users)
    {
        $departments = $this->getUsersByDepartment($users);
        $stats = collect();

        foreach ($departments as $deptName => $deptUsers) {
            $totalTasks = $deptUsers->sum(function ($user) {
                return $this->getUserTaskCount($user);
            });
            $linkedTasks = $deptUsers->sum(function ($user) {
                return $this->getUserLinkedTasksCount($user);
            });
            $unlinkedTasks = $deptUsers->sum(function ($user) {
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
                'users_with_tasks' => $deptUsers->filter(function ($user) {
                    return $this->getUserTaskCount($user) > 0;
                })->count(),
            ]);
        }

        return $stats;
    }

    public function getUserCompletionRate($userId)
    {
        // Get latest week completion rate
        $latestReview = WeeklyTaskReview::where('user_id', $userId)
            ->where('is_submitted', true)
            ->latest('week_start_date')
            ->first();

        return $latestReview ? $latestReview->completion_rate : 0;
    }

    public function getUserAverageCompletionRate($userId)
    {
        // Get average completion rate over last 4 weeks
        $reviews = WeeklyTaskReview::where('user_id', $userId)
            ->where('is_submitted', true)
            ->latest('week_start_date')
            ->take(4)
            ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        return round($reviews->avg('completion_rate'), 2);
    }

    public function getDepartmentAverageCompletionRate($departmentUsers)
    {
        $totalRate = 0;
        $count = 0;

        foreach ($departmentUsers as $user) {
            $rate = $this->getUserAverageCompletionRate($user->id);
            if ($rate > 0) {
                $totalRate += $rate;
                $count++;
            }
        }

        return $count > 0 ? round($totalRate / $count, 2) : 0;
    }

    public function getMyChart()
    {
        if (! isset($this->tasks['users']) || $this->tasks['users']->count() === 0) {
            $this->myChart = [
                'type' => 'pie',
                'data' => [
                    'labels' => ['No Data'],
                    'datasets' => [
                        [
                            'label' => 'Tasks',
                            'data' => [1],
                            'backgroundColor' => ['#e5e7eb'],
                        ],
                    ],
                ],
            ];

            return;
        }

        $linkedTasks = $this->getLinkedTasksCount($this->tasks['users']);
        $unlinkedTasks = $this->getUnlinkedTasksCount($this->tasks['users']);

        $this->myChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Linked Tasks', 'Unlinked Tasks'],
                'datasets' => [
                    [
                        'label' => 'Task Distribution',
                        'data' => [$linkedTasks, $unlinkedTasks],
                        'backgroundColor' => [
                            'rgba(34, 197, 94, 0.8)',  // Green for linked
                            'rgba(245, 158, 11, 0.8)',  // Yellow for unlinked
                        ],
                        'borderColor' => [
                            'rgba(34, 197, 94, 1)',
                            'rgba(245, 158, 11, 1)',
                        ],
                        'borderWidth' => 2,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => [
                            'padding' => 20,
                            'usePointStyle' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function render()
    {

        return view('livewire.admin.trackers.performancetracker', [
            'weeks' => $this->getweeks(),
        ]);
    }
}
