<?php

namespace App\Livewire\Admin\Trackers;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\istrategyInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\iweeklyTaskReviewInterface;
use App\Interfaces\services\ICalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class Departmentaldashboard extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $userDepartment = null;

    protected $budgetRepository;

    protected $departmentRepository;

    protected $issueRepository;

    protected $taskRepository;

    protected $calendarService;

    protected $workplanRepository;

    protected $strategyRepository;

    protected $weeklyTaskReviewRepository;

    public $currentBudgetId;

    public $currentWeekId;

    public $year;

    public $startDate;

    public $endDate;

    public function boot(
        ibudgetInterface $budgetRepository,
        idepartmentInterface $departmentRepository,
        iissuelogInterface $issueRepository,
        itaskInterface $taskRepository,
        ICalendarService $calendarService,
        individualworkplanInterface $workplanRepository,
        istrategyInterface $strategyRepository,
        iweeklyTaskReviewInterface $weeklyTaskReviewRepository
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->departmentRepository = $departmentRepository;
        $this->issueRepository = $issueRepository;
        $this->taskRepository = $taskRepository;
        $this->calendarService = $calendarService;
        $this->workplanRepository = $workplanRepository;
        $this->strategyRepository = $strategyRepository;
        $this->weeklyTaskReviewRepository = $weeklyTaskReviewRepository;
    }

    public function mount()
    {
        // Remove the permission check - it will be in the view
        // if (!Auth::user()->can('departmentaloverview.access')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Departmental Dashboard'],
        ];

        // Get user's department
        $deptUser = $this->departmentRepository->getdepartmentuserbyuserid(Auth::id());

        if (! $deptUser) {
            $this->error('You are not assigned to any department.');

            return redirect()->route('admin.home');
        }

        $this->userDepartment = $deptUser->department;

        // Initialize date ranges
        $this->year = Carbon::now()->year;
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Get current budget
        $budgets = $this->budgetRepository->getbudgets();
        if ($budgets->isNotEmpty()) {
            $this->currentBudgetId = $budgets->first()->id;
        }

        // Get current week
        $weeks = $this->calendarService->getweeks($this->year);
        $currentWeek = $weeks->where('start_date', '>=', $this->startDate)
            ->where('end_date', '<=', $this->endDate)
            ->first();
        if ($currentWeek) {
            $this->currentWeekId = $currentWeek->id;
        }
    }

    // ========== TASKS/TRACKERS METRICS ==========

    public function getTasksMetrics(): array
    {
        if (! $this->userDepartment) {
            return [
                'total' => 0,
                'linked' => 0,
                'unlinked' => 0,
                'completed' => 0,
                'pending' => 0,
                'ongoing' => 0,
                'overdue' => 0,
                'linked_percentage' => 0,
                'completion_rate' => 0,
            ];
        }

        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($this->userDepartment->id);

        // Get tasks created within the date range
        $tasks = $this->taskRepository->gettasksbyuserids($departmentUserIds->toArray())
            ->filter(function ($task) {
                $createdAt = $task->created_at ? Carbon::parse($task->created_at)->format('Y-m-d') : null;

                return $createdAt && $createdAt >= $this->startDate && $createdAt <= $this->endDate;
            });

        // For completed tasks, filter by when they were actually completed (updated_at when status='completed')
        $completedTasks = $tasks->filter(function ($task) {
            if ($task->status !== 'completed') {
                return false;
            }
            $updatedAt = $task->updated_at ? Carbon::parse($task->updated_at)->format('Y-m-d') : null;

            return $updatedAt && $updatedAt >= $this->startDate && $updatedAt <= $this->endDate;
        })->count();

        $totalTasks = $tasks->count();
        $linkedTasks = $tasks->whereNotNull('individualworkplan_id')->count();
        $unlinkedTasks = $tasks->whereNull('individualworkplan_id')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $ongoingTasks = $tasks->where('status', 'ongoing')->count();

        // Overdue calculation
        $overdueTasks = $tasks->whereIn('status', ['pending', 'ongoing'])
            ->filter(function ($task) {
                $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);
                if (! $taskDate) {
                    return false;
                }

                return Carbon::parse($taskDate)->lt(Carbon::now());
            })
            ->count();

        $linkedPercentage = $totalTasks > 0 ? round(($linkedTasks / $totalTasks) * 100, 2) : 0;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        return [
            'total' => $totalTasks,
            'linked' => $linkedTasks,
            'unlinked' => $unlinkedTasks,
            'completed' => $completedTasks,
            'pending' => $pendingTasks,
            'ongoing' => $ongoingTasks,
            'overdue' => $overdueTasks,
            'linked_percentage' => $linkedPercentage,
            'completion_rate' => $completionRate,
        ];
    }

    public function getDepartmentUsersCount(): int
    {
        if (! $this->userDepartment) {
            return 0;
        }

        return $this->departmentRepository->getcountbydepartmentids([$this->userDepartment->id]);
    }

    // ========== BUDGET METRICS ==========

    public function getBudgetMetrics(): array
    {
        if (! $this->userDepartment || ! $this->currentBudgetId) {
            return [
                'total_budget' => 0,
                'total_spent' => 0,
                'total_remaining' => 0,
                'percentage_spent' => 0,
                'items_count' => 0,
            ];
        }

        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId)
            ->where('department_id', $this->userDepartment->id);

        $totalBudget = $budgetItems->sum('total');
        $totalSpent = $this->getDepartmentSpent($budgetItems->pluck('id'));
        $totalRemaining = $totalBudget - $totalSpent;
        $percentageSpent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 2) : 0;

        return [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'percentage_spent' => $percentageSpent,
            'items_count' => $budgetItems->count(),
        ];
    }

    public function getDepartmentSpent($budgetItemIds): float
    {
        if ($budgetItemIds->isEmpty()) {
            return 0;
        }

        return DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->whereIn('purchaserequisitions.budgetitem_id', $budgetItemIds)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->whereBetween('purchaserequisitionawards.created_at', [$this->startDate, $this->endDate])
            ->sum('purchaserequisitionawards.amount');
    }

    // ========== ISSUES METRICS ==========

    public function getIssuesMetrics(): array
    {
        if (! $this->userDepartment) {
            return [
                'total' => 0,
                'open' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'closed' => 0,
            ];
        }

        $issues = $this->issueRepository->getdepartmentissues($this->userDepartment->id);

        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];
    }

    public function getRecentIssues()
    {
        if (! $this->userDepartment) {
            return collect();
        }

        return $this->issueRepository->getdepartmentissues($this->userDepartment->id)
            ->take(5);
    }

    // ========== MONTHLY ASSESSMENT METHODS ==========

    public function getMonthlyTasks($month = null, $year = null): array
    {
        if (! $this->userDepartment) {
            return [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'ongoing' => 0,
                'overdue' => 0,
                'linked' => 0,
                'completion_rate' => 0,
                'linked_percentage' => 0,
            ];
        }

        $targetMonth = $month ?? Carbon::now()->month;
        $targetYear = $year ?? Carbon::now()->year;

        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($this->userDepartment->id);

        // Get tasks created in the month
        $tasks = $this->taskRepository->gettasksbyuserids($departmentUserIds->toArray(), [
            'with' => ['calendarday'],
        ])->filter(function ($task) use ($targetYear, $targetMonth) {
            return $task->created_at && $task->created_at->year == $targetYear && $task->created_at->month == $targetMonth;
        });

        $totalTasks = $tasks->count();

        // For completed tasks, filter by updated_at when status='completed'
        $completedTasks = $tasks->filter(function ($task) use ($targetYear, $targetMonth) {
            if ($task->status !== 'completed') {
                return false;
            }
            $updatedAt = $task->updated_at ? Carbon::parse($task->updated_at) : null;

            return $updatedAt && $updatedAt->year == $targetYear && $updatedAt->month == $targetMonth;
        })->count();

        $pendingTasks = $tasks->where('status', 'pending')->count();
        $ongoingTasks = $tasks->where('status', 'ongoing')->count();

        // Overdue calculation
        $overdueTasks = $tasks->whereIn('status', ['pending', 'ongoing'])
            ->filter(function ($task) {
                $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);
                if (! $taskDate) {
                    return false;
                }

                return Carbon::parse($taskDate)->lt(Carbon::now());
            })
            ->count();

        $linkedTasks = $tasks->whereNotNull('individualworkplan_id')->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
        $linkedPercentage = $totalTasks > 0 ? round(($linkedTasks / $totalTasks) * 100, 2) : 0;

        return [
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'pending' => $pendingTasks,
            'ongoing' => $ongoingTasks,
            'overdue' => $overdueTasks,
            'linked' => $linkedTasks,
            'completion_rate' => $completionRate,
            'linked_percentage' => $linkedPercentage,
        ];
    }

    public function getMonthlyBudgetSpent($month = null, $year = null): float
    {
        if (! $this->userDepartment || ! $this->currentBudgetId) {
            return 0;
        }

        $targetMonth = $month ?? Carbon::now()->month;
        $targetYear = $year ?? Carbon::now()->year;

        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId)
            ->where('department_id', $this->userDepartment->id)
            ->pluck('id');

        if ($budgetItems->isEmpty()) {
            return 0;
        }

        return DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->whereIn('purchaserequisitions.budgetitem_id', $budgetItems)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->whereYear('purchaserequisitionawards.created_at', $targetYear)
            ->whereMonth('purchaserequisitionawards.created_at', $targetMonth)
            ->sum('purchaserequisitionawards.amount');
    }

    public function getMonthlyIssues($month = null, $year = null): array
    {
        if (! $this->userDepartment) {
            return [
                'total' => 0,
                'open' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'closed' => 0,
            ];
        }

        $targetMonth = $month ?? Carbon::now()->month;
        $targetYear = $year ?? Carbon::now()->year;

        $issues = $this->issueRepository->getdepartmentissues($this->userDepartment->id)
            ->filter(function ($issue) use ($targetMonth, $targetYear) {
                return Carbon::parse($issue->created_at)->month == $targetMonth
                    && Carbon::parse($issue->created_at)->year == $targetYear;
            });

        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];
    }

    public function getMonthlyAssessment(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $previousMonth = Carbon::now()->subMonth()->month;
        $previousYear = Carbon::now()->subMonth()->year;
        $twoMonthsAgo = Carbon::now()->subMonths(2)->month;
        $twoMonthsAgoYear = Carbon::now()->subMonths(2)->year;

        // Current month data
        $currentTasks = $this->getMonthlyTasks($currentMonth, $currentYear);
        $currentBudget = $this->getMonthlyBudgetSpent($currentMonth, $currentYear);
        $currentIssues = $this->getMonthlyIssues($currentMonth, $currentYear);

        // Previous month data
        $previousTasks = $this->getMonthlyTasks($previousMonth, $previousYear);
        $previousBudget = $this->getMonthlyBudgetSpent($previousMonth, $previousYear);
        $previousIssues = $this->getMonthlyIssues($previousMonth, $previousYear);

        // Two months ago data
        $twoMonthsAgoTasks = $this->getMonthlyTasks($twoMonthsAgo, $twoMonthsAgoYear);
        $twoMonthsAgoBudget = $this->getMonthlyBudgetSpent($twoMonthsAgo, $twoMonthsAgoYear);
        $twoMonthsAgoIssues = $this->getMonthlyIssues($twoMonthsAgo, $twoMonthsAgoYear);

        // Calculate comparisons
        $calculateChange = function ($current, $previous) {
            $difference = $current - $previous;
            $percentage = $previous > 0 ? round(($difference / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

            return [
                'difference' => $difference,
                'percentage' => $percentage,
                'trend' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'neutral'),
            ];
        };

        return [
            'current_month' => [
                'name' => Carbon::now()->format('F Y'),
                'tasks' => $currentTasks,
                'budget_spent' => $currentBudget,
                'issues' => $currentIssues,
            ],
            'previous_month' => [
                'name' => Carbon::now()->subMonth()->format('F Y'),
                'tasks' => $previousTasks,
                'budget_spent' => $previousBudget,
                'issues' => $previousIssues,
            ],
            'two_months_ago' => [
                'name' => Carbon::now()->subMonths(2)->format('F Y'),
                'tasks' => $twoMonthsAgoTasks,
                'budget_spent' => $twoMonthsAgoBudget,
                'issues' => $twoMonthsAgoIssues,
            ],
            'comparison' => [
                'tasks' => [
                    'total' => $calculateChange($currentTasks['total'], $previousTasks['total']),
                    'completed' => $calculateChange($currentTasks['completed'], $previousTasks['completed']),
                    'completion_rate' => $calculateChange($currentTasks['completion_rate'], $previousTasks['completion_rate']),
                ],
                'budget' => $calculateChange($currentBudget, $previousBudget),
                'issues' => [
                    'total' => $calculateChange($currentIssues['total'], $previousIssues['total']),
                    'resolved' => $calculateChange($currentIssues['resolved'], $previousIssues['resolved']),
                ],
            ],
        ];
    }

    // ========== TEAM PERFORMANCE BREAKDOWN ==========

    public function getTeamPerformance(): array
    {
        if (! $this->userDepartment) {
            return [];
        }

        $departmentUsers = $this->departmentRepository->getusers($this->userDepartment->id);

        $departmentUserIds = $departmentUsers->pluck('user_id');

        // Get current month tasks for all users
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $allTasks = $this->taskRepository->gettasksbyuserids($departmentUserIds->toArray(), [
            'with' => ['calendarday'],
        ])->filter(function ($task) use ($currentYear, $currentMonth) {
            return $task->created_at && $task->created_at->year == $currentYear && $task->created_at->month == $currentMonth;
        });

        // Get issues for all users - filter by date range
        $allIssues = $this->issueRepository->getdepartmentissues($this->userDepartment->id)
            ->filter(function ($issue) use ($currentYear, $currentMonth) {
                $createdAt = $issue->created_at ? Carbon::parse($issue->created_at) : null;

                return $createdAt && $createdAt->year == $currentYear && $createdAt->month == $currentMonth;
            });

        $teamPerformance = [];

        foreach ($departmentUsers as $deptUser) {
            $user = $deptUser->user;
            if (! $user) {
                continue;
            }

            $userTasks = $allTasks->where('user_id', $user->id);
            $userIssues = $allIssues->where('assigned_to', $user->id);

            // For completed tasks, filter by updated_at when status='completed'
            $completedTasks = $userTasks->filter(function ($task) use ($currentYear, $currentMonth) {
                if ($task->status !== 'completed') {
                    return false;
                }
                $updatedAt = $task->updated_at ? Carbon::parse($task->updated_at) : null;

                return $updatedAt && $updatedAt->year == $currentYear && $updatedAt->month == $currentMonth;
            })->count();
            $totalTasks = $userTasks->count();

            // Fix overdue calculation
            $overdueTasks = $userTasks->whereIn('status', ['pending', 'ongoing'])
                ->filter(function ($task) {
                    $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);
                    if (! $taskDate) {
                        return false;
                    }

                    return Carbon::parse($taskDate)->lt(Carbon::now());
                })
                ->count();

            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

            // Get weekly completion rate
            $weeklyReview = $this->weeklyTaskReviewRepository->getreviewbyuserid($user->id, [
                'limit' => 1,
            ])->where('is_submitted', true)->first();

            $weeklyCompletionRate = $weeklyReview ? round((float) $weeklyReview->completion_rate, 2) : 0;

            // Get issue resolution stats
            $resolvedIssues = $userIssues->whereIn('status', ['resolved', 'closed'])->count();
            $totalAssignedIssues = $userIssues->count();
            $resolutionRate = $totalAssignedIssues > 0 ? round(($resolvedIssues / $totalAssignedIssues) * 100, 2) : 0;

            $teamPerformance[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'position' => $deptUser->position ?? 'N/A',
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasks,
                'completion_rate' => $completionRate,
                'weekly_completion_rate' => $weeklyCompletionRate,
                'total_issues' => $totalAssignedIssues,
                'resolved_issues' => $resolvedIssues,
                'resolution_rate' => $resolutionRate,
            ];
        }

        // Sort by completion rate descending
        usort($teamPerformance, function ($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });

        return $teamPerformance;
    }

    // ========== WEEKLY COMPLETION RATE TRENDS ==========

    public function getWeeklyCompletionTrends(): array
    {
        if (! $this->userDepartment) {
            return [
                'weeks' => [],
                'department_average' => [],
                'labels' => [],
            ];
        }

        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($this->userDepartment->id);

        // Get last 8 weeks of data
        $weeks = [];
        $labels = [];
        $departmentAverages = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            $weekLabel = $weekStart->format('M d').' - '.$weekEnd->format('M d');

            $labels[] = $weekLabel;

            $weekReviews = $this->weeklyTaskReviewRepository->getreviewsbyuserids($departmentUserIds->toArray())
                ->where('is_submitted', true)
                ->where('week_start_date', $weekStart->format('Y-m-d'));

            if ($weekReviews->isEmpty()) {
                $departmentAverages[] = 0;
            } else {
                $avgRate = $weekReviews->avg('completion_rate');
                $departmentAverages[] = round((float) $avgRate, 1);
            }
        }

        return [
            'labels' => $labels,
            'department_average' => $departmentAverages,
            'current_week_avg' => $departmentAverages[count($departmentAverages) - 1] ?? 0,
            'previous_week_avg' => $departmentAverages[count($departmentAverages) - 2] ?? 0,
        ];
    }

    // ========== UPCOMING DEADLINES & ALERTS ==========

    public function getUpcomingDeadlines(): array
    {
        if (! $this->userDepartment) {
            return [
                'overdue' => [],
                'due_soon' => [],
                'critical_issues' => [],
            ];
        }

        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($this->userDepartment->id);

        $now = Carbon::now();
        $sevenDaysFromNow = Carbon::now()->addDays(7);

        // Get tasks with calendarday relationship
        $allTasks = $this->taskRepository->gettasksbyuserids($departmentUserIds->toArray(), [
            'status' => ['pending', 'ongoing'],
            'with' => ['user', 'calendarday'],
        ]);

        // Overdue tasks - use calendarday maindate or fallback to end_date if it exists
        $overdueTasks = $allTasks->filter(function ($task) use ($now) {
            $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);
            if (! $taskDate) {
                return false;
            }

            return Carbon::parse($taskDate)->lt($now);
        })->sortBy(function ($task) {
            $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);

            return $taskDate ? Carbon::parse($taskDate)->timestamp : 0;
        })->take(10)->map(function ($task) use ($now) {
            $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);

            return [
                'id' => $task->id,
                'title' => $task->title,
                'user_name' => $task->user->name ?? 'Unknown',
                'end_date' => $taskDate,
                'days_overdue' => $taskDate ? Carbon::parse($taskDate)->diffInDays($now, false) : 0,
                'priority' => $task->priority,
                'status' => $task->status,
            ];
        });

        // Tasks due in next 7 days
        $dueSoonTasks = $allTasks->filter(function ($task) use ($now, $sevenDaysFromNow) {
            $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);
            if (! $taskDate) {
                return false;
            }
            $date = Carbon::parse($taskDate);

            return $date->gte($now) && $date->lte($sevenDaysFromNow);
        })->sortBy(function ($task) {
            $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);

            return $taskDate ? Carbon::parse($taskDate)->timestamp : 0;
        })->take(10)->map(function ($task) use ($now) {
            $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);

            return [
                'id' => $task->id,
                'title' => $task->title,
                'user_name' => $task->user->name ?? 'Unknown',
                'end_date' => $taskDate,
                'days_until_due' => $taskDate ? Carbon::parse($taskDate)->diffInDays($now, false) : 0,
                'priority' => $task->priority,
                'status' => $task->status,
            ];
        });

        // Critical issues (high priority, open or in progress)
        $criticalIssues = $this->issueRepository->getdepartmentissues($this->userDepartment->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->where('priority', 'high')
            ->take(5)
            ->map(function ($issue) use ($now) {
                return [
                    'id' => $issue->id,
                    'title' => $issue->title,
                    'ticketnumber' => $issue->ticketnumber,
                    'priority' => $issue->priority,
                    'status' => $issue->status,
                    'created_at' => $issue->created_at,
                    'days_open' => Carbon::parse($issue->created_at)->diffInDays($now),
                ];
            });

        return [
            'overdue' => $overdueTasks,
            'due_soon' => $dueSoonTasks,
            'critical_issues' => $criticalIssues,
        ];
    }

    // ========== TASK PRIORITY DISTRIBUTION ==========

    public function getTaskPriorityDistribution(): array
    {
        if (! $this->userDepartment) {
            return [
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'total' => 0,
            ];
        }

        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($this->userDepartment->id);

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $tasks = $this->taskRepository->gettasksbyuserids($departmentUserIds->toArray())
            ->filter(function ($task) use ($currentYear, $currentMonth) {
                return $task->created_at && $task->created_at->year == $currentYear && $task->created_at->month == $currentMonth;
            });

        $high = $tasks->where('priority', 'High')->count();
        $medium = $tasks->where('priority', 'Medium')->count();
        $low = $tasks->where('priority', 'Low')->count();
        $total = $tasks->count();

        return [
            'high' => $high,
            'medium' => $medium,
            'low' => $low,
            'total' => $total,
            'high_percentage' => $total > 0 ? round(($high / $total) * 100, 1) : 0,
            'medium_percentage' => $total > 0 ? round(($medium / $total) * 100, 1) : 0,
            'low_percentage' => $total > 0 ? round(($low / $total) * 100, 1) : 0,
        ];
    }

    // ========== ISSUE RESOLUTION METRICS ==========

    public function getIssueResolutionMetrics(): array
    {
        if (! $this->userDepartment) {
            return [
                'avg_turnaround_hours' => 0,
                'avg_turnaround_days' => 0,
                'resolution_rate' => 0,
                'total_resolved' => 0,
                'total_issues' => 0,
                'open_issues' => 0,
                'in_progress_issues' => 0,
                'closed_issues' => 0,
            ];
        }

        // Get issues for this department - filter by date range
        $issues = $this->issueRepository->getdepartmentissues($this->userDepartment->id)
            ->filter(function ($issue) {
                $createdAt = $issue->created_at ? Carbon::parse($issue->created_at)->format('Y-m-d') : null;

                return $createdAt && $createdAt >= $this->startDate && $createdAt <= $this->endDate;
            });

        $totalIssues = $issues->count();
        $resolvedIssues = $issues->filter(fn ($i) => in_array(strtolower($i->status), ['resolved', 'closed']));

        // Calculate average turnaround time from resolved issues
        $resolvedWithDates = $resolvedIssues->filter(function ($issue) {
            return $issue->updated_at !== null;
        });

        $totalHours = 0;
        $count = 0;
        foreach ($resolvedWithDates as $issue) {
            $resolvedAt = $issue->updated_at;
            $hours = Carbon::parse($issue->created_at)->diffInHours(Carbon::parse($resolvedAt));
            $totalHours += $hours;
            $count++;
        }

        $avgHours = $count > 0 ? round($totalHours / $count, 1) : 0;
        $avgDays = $avgHours > 0 ? round($avgHours / 24, 2) : 0;

        $totalResolved = $resolvedIssues->count();
        $resolutionRate = $totalIssues > 0 ? round(($totalResolved / $totalIssues) * 100, 2) : 0;

        // Count issues by status
        $openIssues = $issues->filter(fn ($i) => in_array(strtolower($i->status), ['open', 'pending', 'assigned']))->count();
        $inProgressIssues = $issues->filter(fn ($i) => strtolower($i->status) === 'in_progress')->count();
        $closedIssues = $issues->filter(fn ($i) => strtolower($i->status) === 'closed')->count();

        return [
            'avg_turnaround_hours' => $avgHours,
            'avg_turnaround_days' => $avgDays,
            'resolution_rate' => $resolutionRate,
            'total_resolved' => $totalResolved,
            'total_issues' => $totalIssues,
            'open_issues' => $openIssues,
            'in_progress_issues' => $inProgressIssues,
            'closed_issues' => $closedIssues,
        ];
    }

    // ========== WORKPLAN PROGRESS/GOALS TRACKING ==========
    public function getWorkplanProgress(): array
    {
        if (! $this->userDepartment) {
            return [
                'total_workplans' => 0,
                'approved_workplans' => 0,
                'completion_percentage' => 0,
                'linked_tasks_percentage' => 0,
                'unlinked_tasks_percentage' => 0,
                'linked_tasks_count' => 0,
                'unlinked_tasks_count' => 0,
            ];
        }

        $strategies = $this->strategyRepository->getstrategies();
        $currentStrategy = $strategies->first();

        if (! $currentStrategy) {
            return [
                'total_workplans' => 0,
                'approved_workplans' => 0,
                'completion_percentage' => 0,
                'linked_tasks_percentage' => 0,
                'unlinked_tasks_percentage' => 0,
                'linked_tasks_count' => 0,
                'unlinked_tasks_count' => 0,
            ];
        }

        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($this->userDepartment->id);

        // Get all workplans for department
        $workplans = $this->workplanRepository->getworkplansbyuserids($departmentUserIds->toArray(), [
            'status' => null,
        ])->filter(function ($wp) use ($currentStrategy) {
            return $wp->strategy_id == $currentStrategy->id && $wp->year == $this->year;
        });

        $totalWorkplans = $workplans->count();
        $approvedWorkplans = $workplans->filter(fn ($w) => strtoupper($w->status) === 'APPROVED')->count();
        $completionPercentage = $totalWorkplans > 0 ? round(($approvedWorkplans / $totalWorkplans) * 100, 2) : 0;

        // Get tasks within date range
        $allTasks = $this->taskRepository->gettasksbyuseridsanddaterange($departmentUserIds->toArray(), $this->startDate, $this->endDate);
        $linkedTasks = $allTasks->whereNotNull('individualworkplan_id')->count();
        $unlinkedTasks = $allTasks->whereNull('individualworkplan_id')->count();
        $totalTasks = $allTasks->count();
        $linkedPercentage = $totalTasks > 0 ? round(($linkedTasks / $totalTasks) * 100, 2) : 0;
        $unlinkedPercentage = $totalTasks > 0 ? round(($unlinkedTasks / $totalTasks) * 100, 2) : 0;

        return [
            'total_workplans' => $totalWorkplans,
            'approved_workplans' => $approvedWorkplans,
            'completion_percentage' => $completionPercentage,
            'linked_tasks_percentage' => $linkedPercentage,
            'unlinked_tasks_percentage' => $unlinkedPercentage,
            'linked_tasks_count' => $linkedTasks,
            'unlinked_tasks_count' => $unlinkedTasks,
        ];
    }

    // ========== BUDGET SPENDING TREND CHART ==========
    public function getBudgetSpendingTrend(): array
    {
        if (! $this->userDepartment || ! $this->currentBudgetId) {
            return [
                'labels' => [],
                'spent' => [],
                'budget' => [],
                'remaining' => [],
            ];
        }

        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId)
            ->where('department_id', $this->userDepartment->id);

        $totalBudget = $budgetItems->sum('total');
        $budgetItemIds = $budgetItems->pluck('id');

        $labels = [];
        $spentData = [];
        $budgetData = [];
        $remainingData = [];

        // Get last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $labels[] = $monthName;

            $monthSpent = DB::table('purchaserequisitions')
                ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
                ->whereIn('purchaserequisitions.budgetitem_id', $budgetItemIds)
                ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
                ->whereYear('purchaserequisitionawards.created_at', $date->year)
                ->whereMonth('purchaserequisitionawards.created_at', $date->month)
                ->sum('purchaserequisitionawards.amount');

            $spentData[] = round((float) $monthSpent, 2);
            $budgetData[] = round($totalBudget / 6, 2); // Average monthly budget
            $remainingData[] = round(($totalBudget / 6) - (float) $monthSpent, 2);
        }

        // Calculate cumulative spending
        $cumulativeSpent = [];
        $runningTotal = 0;
        foreach ($spentData as $spent) {
            $runningTotal += $spent;
            $cumulativeSpent[] = round($runningTotal, 2);
        }

        return [
            'labels' => $labels,
            'spent' => $spentData,
            'cumulative_spent' => $cumulativeSpent,
            'budget' => $budgetData,
            'remaining' => $remainingData,
            'total_budget' => $totalBudget,
            'total_spent' => array_sum($spentData),
            'total_remaining' => $totalBudget - array_sum($spentData),
        ];
    }

    // ========== DEPARTMENT HEALTH SCORECARD ==========
    public function getDepartmentHealthScorecard(): array
    {
        if (! $this->userDepartment) {
            return [
                'overall_score' => 0,
                'status' => 'unknown',
                'status_color' => 'gray',
                'factors' => [],
            ];
        }

        $tasksMetrics = $this->getTasksMetrics();
        $budgetMetrics = $this->getBudgetMetrics();
        $issuesMetrics = $this->getIssuesMetrics();
        $issueResolutionMetrics = $this->getIssueResolutionMetrics();
        $workplanProgress = $this->getWorkplanProgress();

        // Calculate individual factor scores (0-100)
        $taskScore = $tasksMetrics['completion_rate']; // Already a percentage

        // Budget score: 100 if under budget, decreasing as spending increases
        $budgetScore = $budgetMetrics['percentage_spent'] <= 100
            ? max(0, 100 - ($budgetMetrics['percentage_spent'] * 0.5))
            : 0;

        // Issue resolution score
        $issueScore = $issueResolutionMetrics['resolution_rate'];

        // Workplan progress score
        $workplanScore = $workplanProgress['completion_percentage'];

        // Overdue tasks penalty
        $overduePenalty = min(20, $tasksMetrics['overdue'] * 2); // Max 20 point penalty

        // Calculate weighted overall score
        $overallScore = (
            ($taskScore * 0.30) +           // 30% weight
            ($budgetScore * 0.20) +         // 20% weight
            ($issueScore * 0.25) +          // 25% weight
            ($workplanScore * 0.15) +       // 15% weight
            (max(0, 100 - $overduePenalty) * 0.10) // 10% weight (inverse of penalty)
        ) - $overduePenalty;

        $overallScore = max(0, min(100, round($overallScore, 1)));

        // Determine status
        if ($overallScore >= 80) {
            $status = 'Excellent';
            $statusColor = 'green';
        } elseif ($overallScore >= 65) {
            $status = 'Good';
            $statusColor = 'blue';
        } elseif ($overallScore >= 50) {
            $status = 'Fair';
            $statusColor = 'yellow';
        } else {
            $status = 'Needs Attention';
            $statusColor = 'red';
        }

        return [
            'overall_score' => $overallScore,
            'status' => $status,
            'status_color' => $statusColor,
            'factors' => [
                [
                    'name' => 'Task Completion',
                    'score' => $taskScore,
                    'weight' => 30,
                    'status' => $taskScore >= 80 ? 'good' : ($taskScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Budget Management',
                    'score' => $budgetScore,
                    'weight' => 20,
                    'status' => $budgetScore >= 80 ? 'good' : ($budgetScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Issue Resolution',
                    'score' => $issueScore,
                    'weight' => 25,
                    'status' => $issueScore >= 80 ? 'good' : ($issueScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Workplan Progress',
                    'score' => $workplanScore,
                    'weight' => 15,
                    'status' => $workplanScore >= 80 ? 'good' : ($workplanScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'On-Time Delivery',
                    'score' => max(0, 100 - $overduePenalty),
                    'weight' => 10,
                    'status' => $tasksMetrics['overdue'] == 0 ? 'good' : ($tasksMetrics['overdue'] <= 3 ? 'fair' : 'poor'),
                ],
            ],
        ];
    }

    // ========== EXPORT/REPORT GENERATION ==========
    public function exportDashboardReport()
    {
        if (! $this->userDepartment) {
            $this->error('Unable to export report: Department not found.');

            return;
        }

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "department_dashboard_report_{$this->userDepartment->name}_{$timestamp}.pdf";

        // Gather all data for the PDF view
        $data = [
            'tasksMetrics' => $this->getTasksMetrics(),
            'budgetMetrics' => $this->getBudgetMetrics(),
            'issuesMetrics' => $this->getIssuesMetrics(),
            'departmentUsersCount' => $this->getDepartmentUsersCount(),
            'departmentHealthScorecard' => $this->getDepartmentHealthScorecard(),
            'monthlyAssessment' => $this->getMonthlyAssessment(),
            'teamPerformance' => $this->getTeamPerformance(),
            'weeklyTrends' => $this->getWeeklyCompletionTrends(),
            'upcomingDeadlines' => $this->getUpcomingDeadlines(),
            'priorityDistribution' => $this->getTaskPriorityDistribution(),
            'issueResolutionMetrics' => $this->getIssueResolutionMetrics(),
            'workplanProgress' => $this->getWorkplanProgress(),
            'budgetSpendingTrend' => $this->getBudgetSpendingTrend(),
            'departmentName' => $this->userDepartment->name,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        // Render as HTML
        $html = view('pdf.department_dashboard', $data)->render();

        // Create PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // ========== HELPER METHODS ==========

    public function getWeeks()
    {
        return $this->calendarService->getweeks($this->year);
    }

    public function getBudgets()
    {
        return $this->budgetRepository->getbudgets();
    }

    public function updatedCurrentWeekId()
    {
        // Update date ranges when week changes
        if ($this->currentWeekId) {
            $weeks = $this->calendarService->getweeks($this->year);
            $selectedWeek = $weeks->find($this->currentWeekId);
            if ($selectedWeek) {
                $this->startDate = Carbon::parse($selectedWeek->start_date)->format('Y-m-d');
                $this->endDate = Carbon::parse($selectedWeek->end_date)->format('Y-m-d');
            }
        }
    }

    public function updatedCurrentBudgetId()
    {
        // Refresh budget when budget changes
    }

    public function render()
    {
        return view('livewire.admin.trackers.departmentaldashboard', [
            'tasksMetrics' => $this->getTasksMetrics(),
            'budgetMetrics' => $this->getBudgetMetrics(),
            'issuesMetrics' => $this->getIssuesMetrics(),
            'departmentUsersCount' => $this->getDepartmentUsersCount(),
            'recentIssues' => $this->getRecentIssues(),
            'weeks' => $this->getWeeks(),
            'budgets' => $this->getBudgets(),
            'monthlyAssessment' => $this->getMonthlyAssessment(),
            'teamPerformance' => $this->getTeamPerformance(),
            'weeklyTrends' => $this->getWeeklyCompletionTrends(),
            'upcomingDeadlines' => $this->getUpcomingDeadlines(),
            'priorityDistribution' => $this->getTaskPriorityDistribution(),
            'issueResolutionMetrics' => $this->getIssueResolutionMetrics(),
            'workplanProgress' => $this->getWorkplanProgress(),
            'budgetSpendingTrend' => $this->getBudgetSpendingTrend(),
            'departmentHealthScorecard' => $this->getDepartmentHealthScorecard(),
        ]);
    }
}
