<?php

namespace App\Livewire\Admin\Trackers;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\istrategyInterface;
use App\Interfaces\repositories\itaskinstanceInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\repositories\iweeklyTaskReviewInterface;
use App\Interfaces\services\ICalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class Organisationdashboard extends Component
{
    use Toast;

    public $breadcrumbs = [];

    protected $budgetRepository;

    protected $departmentRepository;

    protected $issueRepository;

    protected $taskRepository;

    protected $calendarService;

    protected $workplanRepository;

    protected $strategyRepository;

    protected $userRepository;

    protected $taskinstanceRepository;

    protected $weeklyTaskReviewRepository;

    public $currentBudgetId;

    public $currentWeekId;

    public $year;

    public $startDate;

    public $endDate;

    // Filter properties
    public $filterType = 'month'; // 'day', 'week', 'month'

    public $selectedDate;

    public $selectedDepartmentId = null;

    public $showDepartmentModal = false;

    public $selectedDepartmentDetails = null;

    public function boot(
        ibudgetInterface $budgetRepository,
        idepartmentInterface $departmentRepository,
        iissuelogInterface $issueRepository,
        itaskInterface $taskRepository,
        ICalendarService $calendarService,
        individualworkplanInterface $workplanRepository,
        istrategyInterface $strategyRepository,
        iuserInterface $userRepository,
        itaskinstanceInterface $taskinstanceRepository,
        iweeklyTaskReviewInterface $weeklyTaskReviewRepository
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->departmentRepository = $departmentRepository;
        $this->issueRepository = $issueRepository;
        $this->taskRepository = $taskRepository;
        $this->calendarService = $calendarService;
        $this->workplanRepository = $workplanRepository;
        $this->strategyRepository = $strategyRepository;
        $this->userRepository = $userRepository;
        $this->taskinstanceRepository = $taskinstanceRepository;
        $this->weeklyTaskReviewRepository = $weeklyTaskReviewRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Organization Dashboard'],
        ];

        // Initialize date ranges
        $this->year = Carbon::now()->year;
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Get current budget
        $budgets = $this->budgetRepository->getbudgets();
        if ($budgets->isNotEmpty()) {
            $this->currentBudgetId = $budgets->first()->id;
        }

        // Get current week
        $weeks = $this->calendarService->getweeks($this->year);
        $currentWeek = $weeks->where('start_date', '<=', $this->selectedDate)
            ->where('end_date', '>=', $this->selectedDate)
            ->first();
        if ($currentWeek) {
            $this->currentWeekId = $currentWeek->id;
        }
    }

    public function updatedFilterType()
    {
        $this->updateDateRange();
    }

    public function updatedSelectedDate()
    {
        $this->updateDateRange();
    }

    protected function updateDateRange()
    {
        $date = Carbon::parse($this->selectedDate);

        switch ($this->filterType) {
            case 'day':
                $this->startDate = $date->format('Y-m-d');
                $this->endDate = $date->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = $date->startOfWeek()->format('Y-m-d');
                $this->endDate = $date->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = $date->startOfMonth()->format('Y-m-d');
                $this->endDate = $date->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    // ========== OVERALL ORGANIZATION METRICS ==========

    public function getOverallMetrics(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');

        // Total employees
        $totalEmployees = $this->departmentRepository->getcountbydepartmentids($allDepartmentIds->toArray());

        // Tasks
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());
        $tasks = $this->taskRepository->gettasksbyuseridsanddaterange($allUserIds->toArray(), $this->startDate, $this->endDate);

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Budget
        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId);
        $totalBudget = $budgetItems->sum('total');
        $budgetItemIds = $budgetItems->pluck('id');
        $totalSpent = $this->getTotalSpent($budgetItemIds);
        $totalRemaining = $totalBudget - $totalSpent;
        $percentageSpent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        // Issues - linked via assigned_to user, not department_id
        $issues = $this->issueRepository->getissuesbyassigneduserids($allUserIds->toArray(), [
            'dateRange' => ['start' => $this->startDate, 'end' => $this->endDate],
        ]);

        $totalIssues = $issues->count();
        $openIssues = $issues->filter(fn ($i) => in_array(strtolower($i->status), ['open', 'pending', 'assigned']))->count();
        $resolvedIssues = $issues->filter(fn ($i) => in_array(strtolower($i->status), ['resolved', 'closed']))->count();

        return [
            'total_departments' => $departments->count(),
            'total_employees' => $totalEmployees,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'completion_rate' => $completionRate,
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'percentage_spent' => $percentageSpent,
            'total_issues' => $totalIssues,
            'open_issues' => $openIssues,
            'resolved_issues' => $resolvedIssues,
        ];
    }

    // ========== DEPARTMENT BREAKDOWN ==========

    public function getDepartmentBreakdown(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $breakdown = [];

        foreach ($departments as $department) {
            $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($department->id);

            // Tasks - filter by date range to show tasks created/completed within the period
            // This ensures counts change based on filter type (day/week/month)
            $tasks = $this->taskRepository->gettasksbyuserids($departmentUserIds->toArray())
                ->filter(function ($task) {
                    // Include tasks that were created within the date range
                    $createdAt = $task->created_at ? Carbon::parse($task->created_at)->format('Y-m-d') : null;

                    return $createdAt && $createdAt >= $this->startDate && $createdAt <= $this->endDate;
                });

            // For completed tasks, filter by when they were actually completed (updated_at when status='completed')
            $completedTasks = $tasks->filter(function ($task) {
                if ($task->status !== 'completed') {
                    return false;
                }
                // Task was completed if updated_at falls within the date range
                $updatedAt = $task->updated_at ? Carbon::parse($task->updated_at)->format('Y-m-d') : null;

                return $updatedAt && $updatedAt >= $this->startDate && $updatedAt <= $this->endDate;
            })->count();

            $totalTasks = $tasks->count();
            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

            // Budget
            $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId)
                ->where('department_id', $department->id);
            $deptBudget = $budgetItems->sum('total');
            $deptBudgetItemIds = $budgetItems->pluck('id');
            $deptSpent = $this->getTotalSpent($deptBudgetItemIds);
            $deptRemaining = $deptBudget - $deptSpent;
            $deptPercentageSpent = $deptBudget > 0 ? round(($deptSpent / $deptBudget) * 100, 1) : 0;

            // Issues - linked via assigned_to user, not department_id
            $issues = $this->issueRepository->getissuesbyassigneduserids($departmentUserIds->toArray(), [
                'dateRange' => ['start' => $this->startDate, 'end' => $this->endDate],
            ]);

            $totalIssues = $issues->count();
            // Handle mixed case status values
            $openIssues = $issues->filter(function ($issue) {
                return in_array(strtolower($issue->status), ['open', 'pending', 'assigned']);
            })->count();
            $resolvedIssues = $issues->filter(function ($issue) {
                return in_array(strtolower($issue->status), ['resolved', 'closed']);
            })->count();

            // Employees
            $employeeCount = $departmentUserIds->count();

            $breakdown[] = [
                'id' => $department->id,
                'name' => $department->name,
                'employees' => $employeeCount,
                'tasks' => [
                    'total' => $totalTasks,
                    'completed' => $completedTasks,
                    'completion_rate' => $completionRate,
                ],
                'budget' => [
                    'total' => $deptBudget,
                    'spent' => $deptSpent,
                    'remaining' => $deptRemaining,
                    'percentage_spent' => $deptPercentageSpent,
                ],
                'issues' => [
                    'total' => $totalIssues,
                    'open' => $openIssues,
                    'resolved' => $resolvedIssues,
                ],
            ];
        }

        return $breakdown;
    }

    // ========== DEPARTMENT COMPARISON ==========

    public function getDepartmentComparison(): array
    {
        $breakdown = $this->getDepartmentBreakdown();

        return [
            'by_completion_rate' => collect($breakdown)->sortByDesc('tasks.completion_rate')->values()->all(),
            'by_budget_utilization' => collect($breakdown)->sortByDesc('budget.percentage_spent')->values()->all(),
            'by_issue_resolution' => collect($breakdown)->sortByDesc(function ($dept) {
                $total = $dept['issues']['total'];

                return $total > 0 ? ($dept['issues']['resolved'] / $total) * 100 : 0;
            })->values()->all(),
        ];
    }

    // ========== BUDGET DISTRIBUTION ==========

    public function getBudgetDistribution(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $distribution = [];

        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId);
        $totalBudget = $budgetItems->sum('total');

        foreach ($departments as $department) {
            $deptBudgetItems = $budgetItems->where('department_id', $department->id);
            $deptBudget = $deptBudgetItems->sum('total');
            $deptBudgetItemIds = $deptBudgetItems->pluck('id');
            $deptSpent = $this->getTotalSpent($deptBudgetItemIds);

            $distribution[] = [
                'department' => $department->name,
                'allocated' => $deptBudget,
                'spent' => $deptSpent,
                'remaining' => $deptBudget - $deptSpent,
                'percentage' => $totalBudget > 0 ? round(($deptBudget / $totalBudget) * 100, 1) : 0,
                'utilization' => $deptBudget > 0 ? round(($deptSpent / $deptBudget) * 100, 1) : 0,
            ];
        }

        return $distribution;
    }

    // ========== TIME-BASED TRENDS ==========

    public function getTimeBasedTrends(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $trends = [];
        $periods = [];

        // Generate periods based on filter type
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        if ($this->filterType === 'day') {
            // Show hourly breakdown for the day
            for ($i = 0; $i < 24; $i++) {
                $periods[] = $start->copy()->addHours($i)->format('H:00');
            }
        } elseif ($this->filterType === 'week') {
            // Show daily breakdown for the week
            $current = $start->copy();
            while ($current->lte($end)) {
                $periods[] = $current->format('D M d');
                $current->addDay();
            }
        } else {
            // Show weekly breakdown for the month
            $current = $start->copy()->startOfWeek();
            while ($current->lte($end)) {
                $weekEnd = $current->copy()->endOfWeek();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy();
                }
                $periods[] = $current->format('M d').' - '.$weekEnd->format('M d');
                $current->addWeek()->startOfWeek();
            }
        }

        foreach ($periods as $index => $periodLabel) {
            if ($this->filterType === 'day') {
                $periodStart = Carbon::parse($this->startDate)->addHours($index);
                $periodEnd = $periodStart->copy()->addHour();
            } elseif ($this->filterType === 'week') {
                $periodStart = Carbon::parse($this->startDate)->addDays($index);
                $periodEnd = $periodStart->copy()->endOfDay();
            } else {
                $weekStart = Carbon::parse($this->startDate)->addWeeks($index)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy();
                }
                $periodStart = $weekStart;
                $periodEnd = $weekEnd;
            }

            $tasks = $this->taskRepository->gettasksbyuseridsanddaterange($allUserIds->toArray(), $periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d'));

            $trends[] = [
                'period' => $periodLabel,
                'tasks_created' => $tasks->count(),
                'tasks_completed' => $tasks->where('status', 'completed')->count(),
            ];
        }

        return $trends;
    }

    // ========== TOP PERFORMERS ==========

    public function getTopPerformers(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $users = $this->userRepository->getusersbyids($allUserIds->toArray());
        $performers = [];

        foreach ($users as $user) {
            $tasks = $this->taskRepository->gettasksbyuseridanddaterange($user->id, $this->startDate, $this->endDate);

            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 'completed')->count();
            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

            $deptUser = $this->departmentRepository->getdepartmentuserbyuserid($user->id);
            $department = $deptUser ? $deptUser->department : null;

            $performers[] = [
                'id' => $user->id,
                'name' => $user->name,
                'department' => $department ? $department->name : 'N/A',
                'position' => $deptUser ? $deptUser->position : 'N/A',
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $completionRate,
            ];
        }

        return collect($performers)
            ->sortByDesc('completion_rate')
            ->take(10)
            ->values()
            ->all();
    }

    // ========== 1. ORGANIZATION HEALTH SCORECARD ==========

    public function getOrganizationHealthScorecard(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        // Get overall metrics
        $overallMetrics = $this->getOverallMetrics();
        $workplanProgress = $this->getOrganizationWorkplanProgress();
        $issueResolution = $this->getOrganizationIssueResolution();

        // Calculate individual factor scores (0-100)
        $taskScore = round((float) $overallMetrics['completion_rate'], 1);

        // Budget score: Based on utilization - optimal is 50-90% spending
        // Too low spending or overspending both indicate issues
        $percentSpent = (float) $overallMetrics['percentage_spent'];
        if ($overallMetrics['total_budget'] == 0) {
            $budgetScore = 0; // No budget = can't evaluate
        } elseif ($percentSpent >= 50 && $percentSpent <= 90) {
            $budgetScore = 100; // Optimal range
        } elseif ($percentSpent < 50) {
            $budgetScore = round($percentSpent * 2, 1); // Under-utilization
        } elseif ($percentSpent <= 100) {
            $budgetScore = round(100 - (($percentSpent - 90) * 5), 1); // Approaching limit
        } else {
            $budgetScore = max(0, round(100 - (($percentSpent - 100) * 2), 1)); // Over budget
        }

        // Issue resolution score - round it
        $issueScore = round((float) $issueResolution['resolution_rate'], 1);

        // Workplan progress score - round it
        $workplanScore = round((float) $workplanProgress['completion_percentage'], 1);

        // Calculate department health average - ONLY from departments with actual tasks
        $departmentBreakdown = $this->getDepartmentBreakdown();
        $deptsWithTasks = collect($departmentBreakdown)->filter(fn ($d) => $d['tasks']['total'] > 0);
        $avgDeptCompletion = $deptsWithTasks->count() > 0
            ? $deptsWithTasks->avg('tasks.completion_rate')
            : 0;
        $deptHealthScore = round((float) $avgDeptCompletion, 1);

        // Get weights and thresholds from configuration
        $weights = config('organization_health.organization.weights');
        $thresholds = config('organization_health.organization.thresholds');

        // Calculate weighted overall score
        $overallScore = (
            ($taskScore * $weights['task_completion']) +
            ($budgetScore * $weights['budget_management']) +
            ($issueScore * $weights['issue_resolution']) +
            ($workplanScore * $weights['workplan_progress']) +
            ($deptHealthScore * $weights['department_health'])
        );

        $overallScore = max(0, min(100, round($overallScore, 1)));

        // Determine status based on configured thresholds
        if ($overallScore >= $thresholds['excellent']) {
            $status = 'Excellent';
            $statusColor = 'green';
        } elseif ($overallScore >= $thresholds['good']) {
            $status = 'Good';
            $statusColor = 'blue';
        } elseif ($overallScore >= $thresholds['fair']) {
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
                    'weight' => (int) ($weights['task_completion'] * 100),
                    'status' => $taskScore >= 80 ? 'good' : ($taskScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Budget Management',
                    'score' => $budgetScore,
                    'weight' => (int) ($weights['budget_management'] * 100),
                    'status' => $budgetScore >= 80 ? 'good' : ($budgetScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Issue Resolution',
                    'score' => $issueScore,
                    'weight' => (int) ($weights['issue_resolution'] * 100),
                    'status' => $issueScore >= 80 ? 'good' : ($issueScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Workplan Approval Rate',
                    'score' => $workplanScore,
                    'weight' => (int) ($weights['workplan_progress'] * 100),
                    'status' => $workplanScore >= 80 ? 'good' : ($workplanScore >= 60 ? 'fair' : 'poor'),
                ],
                [
                    'name' => 'Department Health',
                    'score' => $deptHealthScore,
                    'weight' => (int) ($weights['department_health'] * 100),
                    'status' => $deptHealthScore >= 80 ? 'good' : ($deptHealthScore >= 60 ? 'fair' : 'poor'),
                ],
            ],
        ];
    }

    // ========== 2. WORKPLAN APPROVAL RATE ACROSS ORGANIZATION ==========
    // NOTE: This method calculates workplan approval rate, not actual progress/completion.
    // It measures what percentage of workplans have been approved, which is used as
    // a proxy for workplan progress in the health scorecard.

    public function getOrganizationWorkplanProgress(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $strategies = $this->strategyRepository->getstrategies();
        $currentStrategy = $strategies->first();

        if (! $currentStrategy) {
            return [
                'total_workplans' => 0,
                'approved_workplans' => 0,
                'completion_percentage' => 0, // Actually approval_percentage, kept for backward compatibility
                'approval_percentage' => 0,    // Alias for clarity
                'linked_tasks_percentage' => 0,
                'unlinked_tasks_percentage' => 0,
                'linked_tasks_count' => 0,
                'unlinked_tasks_count' => 0,
                'total_tasks_count' => 0,
                'by_department' => [],
            ];
        }

        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $totalWorkplans = 0;
        $totalApproved = 0;
        $byDepartment = [];

        foreach ($departments as $department) {
            $deptUserIds = $this->departmentRepository->getuseridsbydepartmentid($department->id);

            // Query ALL workplans for this department (not just approved)
            $workplans = $this->workplanRepository->getworkplansbyuserids($deptUserIds->toArray(), [
                'status' => null, // Get all workplans
            ])->filter(function ($wp) use ($currentStrategy) {
                return $wp->strategy_id == $currentStrategy->id && $wp->year == $this->year;
            });

            $linkedTasks = $this->taskRepository->getlinkedtaskscountbyuserids($deptUserIds->toArray());
            $totalTasks = $this->taskRepository->gettotaltaskscountbyuserids($deptUserIds->toArray());
            $linkedPercentage = $totalTasks > 0 ? round(($linkedTasks / $totalTasks) * 100, 1) : 0;

            $deptTotal = $workplans->count();
            // Count approved with case-insensitive check
            $deptApproved = $workplans->filter(fn ($w) => strtoupper($w->status) === 'APPROVED')->count();
            // Calculate approval rate (not actual completion/progress)
            $deptApprovalRate = $deptTotal > 0 ? round(($deptApproved / $deptTotal) * 100, 1) : 0;

            $totalWorkplans += $deptTotal;
            $totalApproved += $deptApproved;

            $byDepartment[] = [
                'department' => $department->name,
                'total_goals' => $deptTotal,
                'approved' => $deptApproved,  // Add approved count for view
                'progress' => $deptApprovalRate,
            ];
        }

        // Calculate overall approval rate (percentage of approved workplans)
        $overallApprovalRate = $totalWorkplans > 0 ? round(($totalApproved / $totalWorkplans) * 100, 1) : 0;

        // Filter tasks by date range for overall linked/unlinked percentages
        $allTasks = $this->taskRepository->gettasksbyuseridsanddaterange($allUserIds->toArray(), $this->startDate, $this->endDate);
        $allLinkedTasks = $allTasks->whereNotNull('individualworkplan_id')->count();
        $allUnlinkedTasks = $allTasks->whereNull('individualworkplan_id')->count();
        $allTotalTasks = $allTasks->count();
        $overallLinkedPercentage = $allTotalTasks > 0 ? round(($allLinkedTasks / $allTotalTasks) * 100, 1) : 0;
        $overallUnlinkedPercentage = $allTotalTasks > 0 ? round(($allUnlinkedTasks / $allTotalTasks) * 100, 1) : 0;

        return [
            'total_workplans' => $totalWorkplans,
            'approved_workplans' => $totalApproved,
            'completion_percentage' => $overallApprovalRate,
            'approval_percentage' => $overallApprovalRate,
            'linked_tasks_percentage' => $overallLinkedPercentage,
            'unlinked_tasks_percentage' => $overallUnlinkedPercentage,
            'linked_tasks_count' => $allLinkedTasks,
            'unlinked_tasks_count' => $allUnlinkedTasks,
            'total_tasks_count' => $allTotalTasks,
            'by_department' => $byDepartment,
        ];
    }

    // ========== 3. ISSUE RESOLUTION METRICS (ORGANIZATION-WIDE) ==========

    public function getOrganizationIssueResolution(): array
    {
        // Get issues filtered by date range for consistency with other metrics
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $allIssues = $this->issueRepository->getissuesbyassigneduserids($allUserIds->toArray(), [
            'dateRange' => ['start' => $this->startDate, 'end' => $this->endDate],
        ]);

        $resolvedIssues = $allIssues->filter(fn ($i) => in_array(strtolower($i->status), ['resolved', 'closed']));

        // Calculate average turnaround time
        // Note: resolved_at doesn't exist in the model, so we always use updated_at
        $resolvedWithDates = $resolvedIssues->filter(function ($issue) {
            return $issue->updated_at !== null;
        });

        $totalHours = 0;
        $count = 0;
        foreach ($resolvedWithDates as $issue) {
            // Since resolved_at doesn't exist, use updated_at when status is resolved/closed
            $resolvedAt = $issue->updated_at;
            $hours = Carbon::parse($issue->created_at)->diffInHours(Carbon::parse($resolvedAt));
            $totalHours += $hours;
            $count++;
        }

        $avgHours = $count > 0 ? round($totalHours / $count, 1) : 0;
        // Calculate days from the same avgHours to maintain consistency
        $avgDays = $avgHours > 0 ? round($avgHours / 24, 2) : 0; // Use 2 decimal places for better accuracy

        $totalIssues = $allIssues->count();
        $totalResolved = $resolvedIssues->count();
        $resolutionRate = $totalIssues > 0 ? round(($totalResolved / $totalIssues) * 100, 1) : 0;

        // Count open/pending issues (handle mixed case)
        $openIssues = $allIssues->filter(fn ($i) => in_array(strtolower($i->status), ['open', 'pending', 'assigned']))->count();
        $inProgressIssues = $allIssues->filter(fn ($i) => strtolower($i->status) === 'in_progress')->count();

        // Closed issues: issues with status 'closed' (filtered by date range)
        $closedIssues = $allIssues->filter(fn ($i) => strtolower($i->status) === 'closed')->count();

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

    // ========== 4. BUDGET SPENDING TRENDS ==========

    public function getBudgetSpendingTrends(): array
    {
        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId);
        $totalBudget = $budgetItems->sum('total');
        $budgetItemIds = $budgetItems->pluck('id');

        $labels = [];
        $spentData = [];
        $cumulativeSpent = [];

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
        }

        // Calculate cumulative spending
        $runningTotal = 0;
        foreach ($spentData as $spent) {
            $runningTotal += $spent;
            $cumulativeSpent[] = round($runningTotal, 2);
        }

        return [
            'labels' => $labels,
            'spent' => $spentData,
            'cumulative_spent' => $cumulativeSpent,
            'total_budget' => $totalBudget,
            'total_spent' => array_sum($spentData),
            'total_remaining' => $totalBudget - array_sum($spentData),
        ];
    }

    // ========== 5. RISK INDICATORS & ALERTS ==========

    public function getRiskIndicators(): array
    {
        $departmentBreakdown = $this->getDepartmentBreakdown();
        $risks = [];

        foreach ($departmentBreakdown as $dept) {
            $riskLevel = 'low';
            $riskFactors = [];

            // Budget overrun risk
            if ($dept['budget']['percentage_spent'] > 100) {
                $riskLevel = 'critical';
                $riskFactors[] = 'Budget overrun: '.round($dept['budget']['percentage_spent'] - 100, 1).'% over budget';
            } elseif ($dept['budget']['percentage_spent'] > 90) {
                $riskLevel = $riskLevel === 'low' ? 'high' : $riskLevel;
                $riskFactors[] = 'Budget near limit: '.$dept['budget']['percentage_spent'].'% spent';
            }

            // Low completion rate
            if ($dept['tasks']['completion_rate'] < 50) {
                $riskLevel = $riskLevel === 'low' ? 'high' : ($riskLevel === 'high' ? 'critical' : $riskLevel);
                $riskFactors[] = 'Low task completion: '.$dept['tasks']['completion_rate'].'%';
            } elseif ($dept['tasks']['completion_rate'] < 60) {
                $riskLevel = $riskLevel === 'low' ? 'medium' : $riskLevel;
                $riskFactors[] = 'Below average completion: '.$dept['tasks']['completion_rate'].'%';
            }

            // High open issues
            if ($dept['issues']['open'] > 10) {
                $riskLevel = $riskLevel === 'low' ? 'high' : ($riskLevel === 'high' ? 'critical' : $riskLevel);
                $riskFactors[] = 'High number of open issues: '.$dept['issues']['open'];
            }

            if (! empty($riskFactors)) {
                $risks[] = [
                    'department' => $dept['name'],
                    'risk_level' => $riskLevel,
                    'factors' => $riskFactors,
                ];
            }
        }

        return collect($risks)->sortBy(function ($risk) {
            $priority = ['critical' => 3, 'high' => 2, 'medium' => 1, 'low' => 0];

            return $priority[$risk['risk_level']] ?? 0;
        })->reverse()->values()->all();
    }

    // ========== 6. DEPARTMENT DRILL-DOWN ==========

    public function viewDepartment($departmentId)
    {
        $this->selectedDepartmentId = $departmentId;
        $department = $this->departmentRepository->getdepartment($departmentId);

        if ($department) {
            $this->selectedDepartmentDetails = $this->getDepartmentDetails($departmentId);
            $this->showDepartmentModal = true;
        }
    }

    public function closeDepartmentModal()
    {
        $this->showDepartmentModal = false;
        $this->selectedDepartmentDetails = null;
    }

    protected function getDepartmentDetails($departmentId): array
    {
        $department = $this->departmentRepository->getdepartment($departmentId);
        $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($departmentId);

        // Tasks
        $tasks = $this->taskRepository->gettaskswithcalendardaybyuseridsanddaterange($departmentUserIds->toArray(), $this->startDate, $this->endDate);

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $overdueTasks = $tasks->whereIn('status', ['pending', 'ongoing'])
            ->filter(function ($task) {
                $taskDate = $task->calendarday->maindate ?? ($task->end_date ?? null);
                if (! $taskDate) {
                    return false;
                }

                return Carbon::parse($taskDate)->lt(Carbon::now());
            })
            ->count();

        // Budget
        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId)
            ->where('department_id', $departmentId);
        $deptBudget = $budgetItems->sum('total');
        $deptBudgetItemIds = $budgetItems->pluck('id');
        $deptSpent = $this->getTotalSpent($deptBudgetItemIds);

        // Issues - linked via assigned_to user
        $issues = $this->issueRepository->getissuesbyassigneduserids($departmentUserIds->toArray(), [
            'dateRange' => ['start' => $this->startDate, 'end' => $this->endDate],
        ]);

        // Employees
        $employees = $this->departmentRepository->getusers($departmentId)
            ->with('user')
            ->get();

        $totalIssues = $issues->count();
        $openIssues = $issues->filter(fn ($i) => in_array(strtolower($i->status), ['open', 'pending', 'assigned']))->count();
        $resolvedIssues = $issues->filter(fn ($i) => in_array(strtolower($i->status), ['resolved', 'closed']))->count();

        return [
            'department' => $department,
            'employees' => $employees->map(function ($deptUser) {
                return [
                    'name' => $deptUser->user->name ?? 'N/A',
                    'position' => $deptUser->position ?? 'N/A',
                ];
            }),
            'tasks' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'overdue' => $overdueTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
            ],
            'budget' => [
                'total' => $deptBudget,
                'spent' => $deptSpent,
                'remaining' => $deptBudget - $deptSpent,
                'percentage_spent' => $deptBudget > 0 ? round(($deptSpent / $deptBudget) * 100, 1) : 0,
            ],
            'issues' => [
                'total' => $totalIssues,
                'open' => $openIssues,
                'resolved' => $resolvedIssues,
            ],
        ];
    }

    // ========== 7. EXPORT/REPORT GENERATION ==========

    public function exportOrganizationReport()
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "organization_dashboard_report_{$timestamp}.pdf";

        // Gather all data for the PDF view (same as render method)
        $data = [
            'overallMetrics' => $this->getOverallMetrics(),
            'departmentBreakdown' => $this->getDepartmentBreakdown(),
            'departmentComparison' => $this->getDepartmentComparison(),
            'budgetDistribution' => $this->getBudgetDistribution(),
            'timeBasedTrends' => $this->getTimeBasedTrends(),
            'topPerformers' => $this->getTopPerformers(),
            'organizationHealth' => $this->getOrganizationHealthScorecard(),
            'workplanProgress' => $this->getOrganizationWorkplanProgress(),
            'issueResolution' => $this->getOrganizationIssueResolution(),
            'budgetSpendingTrends' => $this->getBudgetSpendingTrends(),
            'riskIndicators' => $this->getRiskIndicators(),
            'comparativeAnalysis' => $this->getComparativeAnalysis(),
            'workloadDistribution' => $this->getWorkloadDistribution(),
            'productivityMetrics' => $this->getProductivityMetrics(),
            'strategicGoals' => $this->getStrategicGoalsTracking(),
            'recentActivity' => $this->getRecentActivity(),
            'performanceHeatmap' => $this->getDepartmentPerformanceHeatmap(),
            'budgetForecast' => $this->getBudgetForecast(),
            'taskApprovalMetrics' => $this->getTaskApprovalMetrics(),
            'supervisorApprovalRates' => $this->getSupervisorApprovalRates(),
            'weeklyTopApprovers' => $this->getWeeklyTopApprovers(),
            'taskHoursProductivity' => $this->getTaskHoursProductivity(),
            'weeklyReviewSummary' => $this->getWeeklyReviewSummary(),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'filterType' => $this->filterType,
            'selectedDate' => $this->selectedDate,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        // Render as HTML
        $html = view('pdf.organization_dashboard', $data)->render();

        // Create PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // ========== 8. COMPARATIVE ANALYSIS ==========

    public function getComparativeAnalysis(): array
    {
        $currentPeriod = [
            'start' => $this->startDate,
            'end' => $this->endDate,
        ];

        // Calculate previous period
        $currentStart = Carbon::parse($this->startDate);
        $currentEnd = Carbon::parse($this->endDate);
        $daysDiff = $currentStart->diffInDays($currentEnd);

        $previousStart = $currentStart->copy()->subDays($daysDiff + 1);
        $previousEnd = $currentStart->copy()->subDay();

        $previousPeriod = [
            'start' => $previousStart->format('Y-m-d'),
            'end' => $previousEnd->format('Y-m-d'),
        ];

        // Get metrics for both periods
        $currentMetrics = $this->getOverallMetrics();

        // Get previous period metrics
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $prevTasks = $this->taskRepository->gettasksbyuseridsanddaterange($allUserIds->toArray(), $previousPeriod['start'], $previousPeriod['end']);

        $prevBudgetItemIds = $this->budgetRepository->getbudgetitems($this->currentBudgetId)->pluck('id');
        $prevSpent = DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->whereIn('purchaserequisitions.budgetitem_id', $prevBudgetItemIds)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->whereBetween('purchaserequisitionawards.created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->sum('purchaserequisitionawards.amount');

        $prevIssues = $this->issueRepository->getissuesbyassigneduserids($allUserIds->toArray(), [
            'dateRange' => ['start' => $previousPeriod['start'], 'end' => $previousPeriod['end']],
        ]);

        $previousMetrics = [
            'total_tasks' => $prevTasks->count(),
            'completed_tasks' => $prevTasks->where('status', 'completed')->count(),
            'completion_rate' => $prevTasks->count() > 0 ? round(($prevTasks->where('status', 'completed')->count() / $prevTasks->count()) * 100, 1) : 0,
            'total_spent' => (float) $prevSpent,
            'total_issues' => $prevIssues->count(),
            'resolved_issues' => $prevIssues->filter(fn ($i) => in_array(strtolower($i->status), ['resolved', 'closed']))->count(),
        ];

        // Calculate differences
        $tasksDiff = $currentMetrics['total_tasks'] - $previousMetrics['total_tasks'];
        $tasksPercent = $previousMetrics['total_tasks'] > 0
            ? round(($tasksDiff / $previousMetrics['total_tasks']) * 100, 1)
            : 0;

        $completionDiff = $currentMetrics['completion_rate'] - $previousMetrics['completion_rate'];
        $spentDiff = $currentMetrics['total_spent'] - $previousMetrics['total_spent'];
        $spentPercent = $previousMetrics['total_spent'] > 0
            ? round(($spentDiff / $previousMetrics['total_spent']) * 100, 1)
            : 0;

        $issuesDiff = $currentMetrics['total_issues'] - $previousMetrics['total_issues'];
        $resolvedDiff = $currentMetrics['resolved_issues'] - $previousMetrics['resolved_issues'];

        return [
            'current_period' => $currentMetrics,
            'previous_period' => $previousMetrics,
            'comparison' => [
                'tasks' => [
                    'difference' => $tasksDiff,
                    'percentage' => $tasksPercent,
                    'trend' => $tasksDiff > 0 ? 'up' : ($tasksDiff < 0 ? 'down' : 'stable'),
                ],
                'completion_rate' => [
                    'difference' => $completionDiff,
                    'trend' => $completionDiff > 0 ? 'up' : ($completionDiff < 0 ? 'down' : 'stable'),
                ],
                'budget' => [
                    'difference' => $spentDiff,
                    'percentage' => $spentPercent,
                    'trend' => $spentDiff > 0 ? 'up' : ($spentDiff < 0 ? 'down' : 'stable'),
                ],
                'issues' => [
                    'difference' => $issuesDiff,
                    'trend' => $issuesDiff > 0 ? 'up' : ($issuesDiff < 0 ? 'down' : 'stable'),
                ],
                'resolved' => [
                    'difference' => $resolvedDiff,
                    'trend' => $resolvedDiff > 0 ? 'up' : ($resolvedDiff < 0 ? 'down' : 'stable'),
                ],
            ],
        ];
    }

    // ========== 9. WORKLOAD DISTRIBUTION ==========

    public function getWorkloadDistribution(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $distribution = [];

        foreach ($departments as $department) {
            $departmentUserIds = $this->departmentRepository->getuseridsbydepartmentid($department->id);
            $employeeCount = $departmentUserIds->count();

            // Get tasks created within the date range
            $tasks = $this->taskRepository->gettasksbyuseridsanddaterange($departmentUserIds->toArray(), $this->startDate, $this->endDate);
            $totalTasks = $tasks->count();

            // Get issues assigned within the date range
            $issues = $this->issueRepository->getissuesbyassigneduserids($departmentUserIds->toArray(), [
                'dateRange' => ['start' => $this->startDate, 'end' => $this->endDate],
            ]);
            $totalIssues = $issues->count();

            // Total workload = tasks + issues
            $totalWorkload = $totalTasks + $totalIssues;
            $avgWorkloadPerEmployee = $employeeCount > 0 ? round($totalWorkload / $employeeCount, 1) : 0;

            // Determine workload status based on combined tasks + issues
            $workloadStatus = 'balanced';
            if ($avgWorkloadPerEmployee > 20) {
                $workloadStatus = 'overloaded';
            } elseif ($avgWorkloadPerEmployee < 5) {
                $workloadStatus = 'underloaded';
            }

            $distribution[] = [
                'department' => $department->name,
                'employees' => $employeeCount,
                'total_tasks' => $totalTasks,
                'total_issues' => $totalIssues,
                'total_workload' => $totalWorkload,
                'avg_workload_per_employee' => $avgWorkloadPerEmployee,
                'workload_status' => $workloadStatus,
            ];
        }

        return $distribution;
    }

    // ========== 10. PRODUCTIVITY METRICS ==========

    public function getProductivityMetrics(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $totalEmployees = $allUserIds->count();
        $overallMetrics = $this->getOverallMetrics();

        $tasksPerEmployee = $totalEmployees > 0 ? round($overallMetrics['total_tasks'] / $totalEmployees, 1) : 0;
        $completedPerEmployee = $totalEmployees > 0 ? round($overallMetrics['completed_tasks'] / $totalEmployees, 1) : 0;

        // Cost per task completion
        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId);
        $totalBudget = $budgetItems->sum('total');
        $costPerTask = $overallMetrics['completed_tasks'] > 0
            ? round($overallMetrics['total_spent'] / $overallMetrics['completed_tasks'], 2)
            : 0;

        // Efficiency score (combination of completion rate and budget efficiency)
        $efficiencyScore = (
            ($overallMetrics['completion_rate'] * 0.6) +
            (max(0, 100 - $overallMetrics['percentage_spent']) * 0.4)
        );

        return [
            'tasks_per_employee' => $tasksPerEmployee,
            'completed_per_employee' => $completedPerEmployee,
            'cost_per_task_completion' => $costPerTask,
            'efficiency_score' => round($efficiencyScore, 1),
            'productivity_index' => round(($overallMetrics['completion_rate'] / 100) * ($tasksPerEmployee / 10) * 100, 1),
        ];
    }

    // ========== 11. STRATEGIC GOALS/KPI TRACKING ==========

    public function getStrategicGoalsTracking(): array
    {
        $strategies = $this->strategyRepository->getstrategies();
        $currentStrategy = $strategies->first();

        if (! $currentStrategy) {
            return [
                'total_goals' => 0,
                'completed_goals' => 0,
                'completion_percentage' => 0,
                'by_department' => [],
            ];
        }

        $departments = $this->departmentRepository->getdepartments();
        $totalGoals = 0;
        $completedGoals = 0;
        $byDepartment = [];

        foreach ($departments as $department) {
            $workplans = $this->workplanRepository->getapprovedworkplansbydepartment(
                $department->id,
                $currentStrategy->id,
                $this->year
            );

            $deptUserIds = $this->departmentRepository->getuseridsbydepartmentid($department->id);
            $linkedTasks = $this->taskRepository->gettasksbyuserids($deptUserIds->toArray(), [
                'whereNotNull' => ['individualworkplan_id'],
            ]);

            $completedLinkedTasks = $linkedTasks->where('status', 'completed')->count();
            $totalLinkedTasks = $linkedTasks->count();
            $goalProgress = $totalLinkedTasks > 0
                ? round(($completedLinkedTasks / $totalLinkedTasks) * 100, 1)
                : 0;

            $deptTotal = $workplans->count();
            $totalGoals += $deptTotal;

            $byDepartment[] = [
                'department' => $department->name,
                'total_goals' => $deptTotal,
                'progress' => $goalProgress,
            ];
        }

        return [
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'completion_percentage' => $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 1) : 0,
            'by_department' => $byDepartment,
        ];
    }

    // ========== 12. RECENT ACTIVITY FEED ==========

    public function getRecentActivity(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $activities = [];

        // Recent task completions
        $recentTasks = $this->taskRepository->gettasksbyuserids($allUserIds->toArray(), [
            'status' => 'completed',
            'orderBy' => ['column' => 'updated_at', 'direction' => 'desc'],
            'with' => ['user'],
            'limit' => 5,
        ]);

        foreach ($recentTasks as $task) {
            $deptUser = $this->departmentRepository->getdepartmentuserbyuserid($task->user_id);
            $activities[] = [
                'type' => 'task_completed',
                'title' => 'Task completed: '.\Illuminate\Support\Str::limit($task->title, 40),
                'user' => $task->user->name ?? 'Unknown',
                'department' => $deptUser ? $deptUser->department->name : 'N/A',
                'timestamp' => $task->updated_at,
                'icon' => 'check-circle',
                'color' => 'green',
            ];
        }

        // Recent issue resolutions - linked via assigned_to user
        $recentIssues = $this->issueRepository->getissuesbyassigneduserids($allUserIds->toArray(), [
            'status' => ['resolved', 'closed'],
            'orderBy' => ['column' => 'updated_at', 'direction' => 'desc'],
        ])->take(5);

        foreach ($recentIssues as $issue) {
            $deptUser = $this->departmentRepository->getdepartmentuserbyuserid($issue->assigned_to);
            $activities[] = [
                'type' => 'issue_resolved',
                'title' => 'Issue resolved: '.\Illuminate\Support\Str::limit($issue->title, 40),
                'user' => $this->userRepository->getuserbyid($issue->assigned_to)->name ?? 'Unknown',
                'department' => $deptUser && $deptUser->department ? $deptUser->department->name : 'N/A',
                'timestamp' => $issue->updated_at,
                'icon' => 'exclamation-circle',
                'color' => 'blue',
            ];
        }

        // Recent budget transactions
        $budgetItemIds = $this->budgetRepository->getbudgetitems($this->currentBudgetId)->pluck('id');
        $recentTransactions = DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->whereIn('purchaserequisitions.budgetitem_id', $budgetItemIds)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->orderBy('purchaserequisitionawards.created_at', 'desc')
            ->take(5)
            ->select('purchaserequisitionawards.amount', 'purchaserequisitionawards.created_at')
            ->get();

        foreach ($recentTransactions as $transaction) {
            $activities[] = [
                'type' => 'budget_transaction',
                'title' => 'Budget transaction: $'.number_format($transaction->amount, 2),
                'user' => 'Finance',
                'department' => 'All',
                'timestamp' => $transaction->created_at,
                'icon' => 'currency-dollar',
                'color' => 'purple',
            ];
        }

        return collect($activities)
            ->sortByDesc('timestamp')
            ->take(15)
            ->values()
            ->all();
    }

    // ========== 13. DEPARTMENT PERFORMANCE HEATMAP ==========

    public function getDepartmentPerformanceHeatmap(): array
    {
        $departmentBreakdown = $this->getDepartmentBreakdown();
        $heatmap = [];

        // Get configurable weights and thresholds
        $weights = config('organization_health.department.weights');
        $thresholds = config('organization_health.department.thresholds');

        foreach ($departmentBreakdown as $dept) {
            $hasTasks = $dept['tasks']['total'] > 0;
            $hasBudget = $dept['budget']['total'] > 0;
            $hasIssues = $dept['issues']['total'] > 0;

            // Calculate completion rate - only if there are tasks
            $completionRate = $hasTasks ? $dept['tasks']['completion_rate'] : null;

            // Calculate budget utilization - only if there's budget
            $budgetUtilization = $hasBudget ? $dept['budget']['percentage_spent'] : null;

            // Calculate issue resolution - only if there are issues
            $issueResolution = $hasIssues
                ? round(($dept['issues']['resolved'] / $dept['issues']['total']) * 100, 1)
                : null;

            // Calculate performance score using configurable weights
            // Only use weights for metrics that have data, and normalize accordingly
            $scoreComponents = [];
            $totalWeight = 0;
            $availableWeights = [];

            if ($hasTasks) {
                $availableWeights['task_completion'] = $weights['task_completion'];
            }

            if ($hasBudget) {
                $availableWeights['budget_management'] = $weights['budget_management'];
            }

            if ($hasIssues) {
                $availableWeights['issue_resolution'] = $weights['issue_resolution'];
            }

            // Normalize weights so they sum to 1.0 when only considering available metrics
            $sumAvailableWeights = array_sum($availableWeights);
            $normalizedWeights = $sumAvailableWeights > 0
                ? array_map(fn ($w) => $w / $sumAvailableWeights, $availableWeights)
                : [];

            // Calculate weighted scores
            if (isset($normalizedWeights['task_completion']) && $hasTasks) {
                $scoreComponents[] = $completionRate * $normalizedWeights['task_completion'];
                $totalWeight += $normalizedWeights['task_completion'];
            }

            if (isset($normalizedWeights['budget_management']) && $hasBudget) {
                // Budget management score: optimal range is 50-90%
                // Score formula: penalize both under-utilization (waste) and over-utilization (risk)
                if ($budgetUtilization <= 50) {
                    // Under 50%: linear scale from 0-50% gets 0-50 points
                    $budgetScore = ($budgetUtilization / 50) * 50;
                } elseif ($budgetUtilization <= 90) {
                    // 50-90%: optimal range, gets 50-100 points
                    $budgetScore = 50 + (($budgetUtilization - 50) / 40) * 50;
                } elseif ($budgetUtilization <= 100) {
                    // 90-100%: still acceptable but less optimal, gets 100-90 points
                    $budgetScore = 100 - (($budgetUtilization - 90) / 10) * 10;
                } else {
                    // Over 100%: over budget, penalize heavily
                    $budgetScore = max(0, 90 - (($budgetUtilization - 100) * 2));
                }
                $scoreComponents[] = $budgetScore * $normalizedWeights['budget_management'];
                $totalWeight += $normalizedWeights['budget_management'];
            }

            if (isset($normalizedWeights['issue_resolution']) && $hasIssues) {
                $scoreComponents[] = $issueResolution * $normalizedWeights['issue_resolution'];
                $totalWeight += $normalizedWeights['issue_resolution'];
            }

            // Calculate weighted score
            if ($totalWeight > 0) {
                $performanceScore = array_sum($scoreComponents) / $totalWeight;
            } else {
                // No data available - show as "no activity"
                $performanceScore = 0;
            }

            // Determine heat level using configurable thresholds
            $hasAnyData = $hasTasks || $hasBudget || $hasIssues;

            if (! $hasAnyData) {
                $heatLevel = 'no_data';
                $heatColor = 'gray';
            } elseif ($performanceScore >= $thresholds['excellent']) {
                $heatLevel = 'excellent';
                $heatColor = 'green';
            } elseif ($performanceScore >= $thresholds['good']) {
                $heatLevel = 'good';
                $heatColor = 'blue';
            } elseif ($performanceScore >= $thresholds['fair']) {
                $heatLevel = 'fair';
                $heatColor = 'yellow';
            } else {
                $heatLevel = 'poor';
                $heatColor = 'red';
            }

            $heatmap[] = [
                'department' => $dept['name'],
                'performance_score' => round($performanceScore, 1),
                'heat_level' => $heatLevel,
                'heat_color' => $heatColor,
                'completion_rate' => $completionRate,
                'budget_utilization' => $budgetUtilization,
                'issue_resolution' => $issueResolution,
                'has_tasks' => $hasTasks,
                'has_budget' => $hasBudget,
                'has_issues' => $hasIssues,
                'has_any_data' => $hasAnyData,
                'total_tasks' => $dept['tasks']['total'],
                'completed_tasks' => $dept['tasks']['completed'],  // Add completed tasks count for view
                'total_budget' => $dept['budget']['total'],
                'total_issues' => $dept['issues']['total'],
            ];
        }

        // Sort: departments with data first (by score), then without data
        return collect($heatmap)
            ->sortByDesc(function ($dept) {
                return $dept['has_any_data'] ? $dept['performance_score'] : -1;
            })
            ->values()
            ->all();
    }

    // ========== 15. BUDGET FORECAST/PROJECTIONS ==========

    public function getBudgetForecast(): array
    {
        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId);
        $totalBudget = $budgetItems->sum('total');
        $budgetItemIds = $budgetItems->pluck('id');

        // Get last 3 months spending for trend analysis
        $monthlySpending = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthSpent = DB::table('purchaserequisitions')
                ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
                ->whereIn('purchaserequisitions.budgetitem_id', $budgetItemIds)
                ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
                ->whereYear('purchaserequisitionawards.created_at', $date->year)
                ->whereMonth('purchaserequisitionawards.created_at', $date->month)
                ->sum('purchaserequisitionawards.amount');

            $monthlySpending[] = (float) $monthSpent;
        }

        // Calculate average monthly spending
        $avgMonthlySpending = count($monthlySpending) > 0
            ? array_sum($monthlySpending) / count($monthlySpending)
            : 0;

        // Get current spending - total spending for the budget (not filtered by date range)
        // This should represent year-to-date or all-time spending for accurate projections
        $currentSpent = DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->whereIn('purchaserequisitions.budgetitem_id', $budgetItemIds)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->sum('purchaserequisitionawards.amount');

        // Calculate remaining months in year
        $remainingMonths = 12 - Carbon::now()->month;

        // Projected spending
        $projectedSpending = $currentSpent + ($avgMonthlySpending * $remainingMonths);

        // Burn rate (months until budget exhausted)
        $burnRate = $avgMonthlySpending > 0
            ? round(($totalBudget - $currentSpent) / $avgMonthlySpending, 1)
            : 0;

        // Estimated completion date
        $estimatedCompletion = $avgMonthlySpending > 0 && $burnRate > 0
            ? Carbon::now()->addMonths(floor($burnRate))->format('M Y')
            : 'N/A';

        return [
            'total_budget' => $totalBudget,
            'current_spent' => round((float) $currentSpent, 2),
            'remaining' => round((float) ($totalBudget - $currentSpent), 2),
            'avg_monthly_spending' => round($avgMonthlySpending, 2),
            'projected_spending' => round($projectedSpending, 2),
            'projected_remaining' => round(($totalBudget - $projectedSpending), 2),
            'burn_rate_months' => $burnRate,
            'estimated_completion' => $estimatedCompletion,
            'risk_level' => $projectedSpending > $totalBudget ? 'high' : ($projectedSpending > ($totalBudget * 0.9) ? 'medium' : 'low'),
        ];
    }

    // ========== 16. TASK APPROVAL METRICS ==========

    public function getTaskApprovalMetrics(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $tasks = $this->taskRepository->gettasksbyuseridsanddaterange($allUserIds->toArray(), $this->startDate, $this->endDate);

        $totalTasks = $tasks->count();
        $pendingApproval = $tasks->where('approvalstatus', 'pending')->count();
        $approvedTasks = $tasks->where('approvalstatus', 'Approved')->count();
        $rejectedTasks = $tasks->where('approvalstatus', 'Rejected')->count();

        $approvalRate = $totalTasks > 0 ? round(($approvedTasks / $totalTasks) * 100, 1) : 0;
        $rejectionRate = $totalTasks > 0 ? round(($rejectedTasks / $totalTasks) * 100, 1) : 0;
        $pendingRate = $totalTasks > 0 ? round(($pendingApproval / $totalTasks) * 100, 1) : 0;

        // Average time to approval (for approved tasks)
        $approvedWithTime = $tasks->where('approvalstatus', 'Approved')
            ->whereNotNull('approved_by')
            ->filter(function ($task) {
                return $task->created_at && $task->updated_at;
            });

        $avgApprovalHours = 0;
        if ($approvedWithTime->count() > 0) {
            $totalHours = $approvedWithTime->sum(function ($task) {
                return Carbon::parse($task->created_at)->diffInHours($task->updated_at);
            });
            $avgApprovalHours = round($totalHours / $approvedWithTime->count(), 1);
        }

        return [
            'total_tasks' => $totalTasks,
            'pending_approval' => $pendingApproval,
            'approved_tasks' => $approvedTasks,
            'rejected_tasks' => $rejectedTasks,
            'approval_rate' => $approvalRate,
            'rejection_rate' => $rejectionRate,
            'pending_rate' => $pendingRate,
            'avg_approval_hours' => $avgApprovalHours,
            'avg_approval_days' => round($avgApprovalHours / 24, 1),
        ];
    }

    // ========== 17. SUPERVISOR APPROVAL RATES ==========

    public function getSupervisorApprovalRates(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        // Get all tasks with approvers
        $tasks = $this->taskRepository->gettasksbyuserids($allUserIds->toArray(), [
            'whereNotNull' => ['approved_by'],
        ])->filter(function ($task) {
            return $task->created_at >= $this->startDate && $task->created_at <= $this->endDate;
        });

        // Group by approver
        $approverStats = $tasks->groupBy('approved_by')->map(function ($approverTasks, $approverId) {
            $approver = $this->userRepository->getuserbyid($approverId);
            $approverDept = $this->departmentRepository->getdepartmentuserbyuserid($approverId);

            $approved = $approverTasks->where('approvalstatus', 'Approved')->count();
            $rejected = $approverTasks->where('approvalstatus', 'Rejected')->count();
            $total = $approverTasks->count();

            return [
                'approver_id' => $approverId,
                'approver_name' => $approver ? $approver->name : 'Unknown',
                'department' => $approverDept && $approverDept->department ? $approverDept->department->name : 'N/A',
                'position' => $approverDept ? ($approverDept->position ?? 'Supervisor') : 'N/A',
                'total_reviewed' => $total,
                'approved' => $approved,
                'rejected' => $rejected,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
            ];
        })->sortByDesc('total_reviewed')->values()->all();

        return $approverStats;
    }

    // ========== 18. WEEKLY TOP APPROVERS LOG ==========

    public function getWeeklyTopApprovers(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        $weeklyApprovers = [];

        // Get last 4 weeks of data
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            $weekLabel = $weekStart->format('M d').' - '.$weekEnd->format('M d');

            $weekTasks = $this->taskRepository->gettasksbyuserids($allUserIds->toArray(), [
                'whereNotNull' => ['approved_by'],
                'approvalstatus' => 'Approved',
            ])->filter(function ($task) use ($weekStart, $weekEnd) {
                return $task->updated_at >= $weekStart && $task->updated_at <= $weekEnd;
            });

            // Group by approver and get top 5
            $topApprovers = $weekTasks->groupBy('approved_by')->map(function ($tasks, $approverId) {
                $approver = $this->userRepository->getuserbyid($approverId);
                $approverDept = $this->departmentRepository->getdepartmentuserbyuserid($approverId);

                return [
                    'approver_id' => $approverId,
                    'name' => $approver ? $approver->name : 'Unknown',
                    'department' => $approverDept && $approverDept->department ? $approverDept->department->name : 'N/A',
                    'tasks_approved' => $tasks->count(),
                ];
            })->sortByDesc('tasks_approved')->take(5)->values()->all();

            $weeklyApprovers[] = [
                'week_label' => $weekLabel,
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'total_approvals' => $weekTasks->count(),
                'top_approvers' => $topApprovers,
            ];
        }

        return $weeklyApprovers;
    }

    // ========== 19. TASK HOURS PRODUCTIVITY (USING TASKINSTANCE) ==========

    public function getTaskHoursProductivity(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        // Get all task IDs for the organization
        $taskIds = $this->taskRepository->gettaskidsbyuseridsanddaterange($allUserIds->toArray(), $this->startDate, $this->endDate);

        // Get task instances
        $taskInstances = $this->taskinstanceRepository->getinstancesbytaskids($taskIds->toArray())
            ->filter(function ($instance) {
                return $instance->date >= $this->startDate && $instance->date <= $this->endDate;
            });

        $totalPlannedHours = $taskInstances->sum('planned_hours');
        $totalWorkedHours = $taskInstances->sum('worked_hours');
        $efficiencyRate = $totalPlannedHours > 0
            ? round(($totalWorkedHours / $totalPlannedHours) * 100, 1)
            : 0;

        // By department breakdown
        $byDepartment = [];
        foreach ($departments as $department) {
            $deptUserIds = $this->departmentRepository->getuseridsbydepartmentid($department->id);
            $deptTaskIds = $this->taskRepository->gettaskidsbyuseridsanddaterange($deptUserIds->toArray(), $this->startDate, $this->endDate);

            $deptInstances = $this->taskinstanceRepository->getinstancesbytaskids($deptTaskIds->toArray())
                ->filter(function ($instance) {
                    return $instance->date >= $this->startDate && $instance->date <= $this->endDate;
                });

            $deptPlanned = $deptInstances->sum('planned_hours');
            $deptWorked = $deptInstances->sum('worked_hours');

            $byDepartment[] = [
                'department' => $department->name,
                'planned_hours' => round((float) $deptPlanned, 1),
                'worked_hours' => round((float) $deptWorked, 1),
                'efficiency' => $deptPlanned > 0 ? round(($deptWorked / $deptPlanned) * 100, 1) : 0,
            ];
        }

        // Status breakdown from task instances
        $ongoingCount = $taskInstances->where('status', 'ongoing')->count();
        $rolledOverCount = $taskInstances->where('status', 'rolled_over')->count();

        // For completed, count from tasks table where status='completed' and updated_at is within date range
        $completedTasks = $this->taskRepository->gettasksbyuserids($allUserIds->toArray())
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$this->startDate, $this->endDate]);
        $completedCount = $completedTasks->count();

        return [
            'total_planned_hours' => round((float) $totalPlannedHours, 1),
            'total_worked_hours' => round((float) $totalWorkedHours, 1),
            'efficiency_rate' => $efficiencyRate,
            'variance_hours' => round((float) $totalWorkedHours - (float) $totalPlannedHours, 1),
            'by_department' => $byDepartment,
            'instance_status' => [
                'ongoing' => $ongoingCount,
                'completed' => $completedCount,
                'rolled_over' => $rolledOverCount,
            ],
        ];
    }

    // ========== 20. WEEKLY REVIEW SUMMARY (USING WEEKLYTASKREVIEW) ==========

    public function getWeeklyReviewSummary(): array
    {
        $departments = $this->departmentRepository->getdepartments();
        $allDepartmentIds = $departments->pluck('id');
        $allUserIds = $this->departmentRepository->getuseridsbydepartmentids($allDepartmentIds->toArray());

        // Get last 4 weeks of submitted reviews
        $fourWeeksAgo = Carbon::now()->subWeeks(4)->startOfWeek();

        $reviews = $this->weeklyTaskReviewRepository->getreviewsbyuserids($allUserIds->toArray())
            ->where('is_submitted', true)
            ->where('week_start_date', '>=', $fourWeeksAgo);

        $totalReviews = $reviews->count();
        $avgCompletionRate = $totalReviews > 0 ? round($reviews->avg('completion_rate'), 1) : 0;
        $avgHoursPlanned = $totalReviews > 0 ? round($reviews->avg('total_hours_planned'), 1) : 0;
        $avgHoursCompleted = $totalReviews > 0 ? round($reviews->avg('total_hours_completed'), 1) : 0;

        // Participation rate (users who submitted reviews vs total users)
        $usersWithReviews = $reviews->pluck('user_id')->unique()->count();
        $totalUsers = $allUserIds->count();
        $participationRate = $totalUsers > 0 ? round(($usersWithReviews / $totalUsers) * 100, 1) : 0;

        // Weekly trends
        $weeklyTrends = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekLabel = $weekStart->format('M d');

            $weekReviews = $reviews->where('week_start_date', $weekStart->format('Y-m-d'));

            $weeklyTrends[] = [
                'week' => $weekLabel,
                'submissions' => $weekReviews->count(),
                'avg_completion' => $weekReviews->count() > 0 ? round($weekReviews->avg('completion_rate'), 1) : 0,
            ];
        }

        // By department
        $byDepartment = [];
        foreach ($departments as $department) {
            $deptUserIds = $this->departmentRepository->getuseridsbydepartmentid($department->id);
            $deptReviews = $reviews->whereIn('user_id', $deptUserIds);

            $byDepartment[] = [
                'department' => $department->name,
                'submissions' => $deptReviews->count(),
                'avg_completion_rate' => $deptReviews->count() > 0 ? round($deptReviews->avg('completion_rate'), 1) : 0,
                'total_users' => $deptUserIds->count(),
                'participation_rate' => $deptUserIds->count() > 0
                    ? round(($deptReviews->pluck('user_id')->unique()->count() / $deptUserIds->count()) * 100, 1)
                    : 0,
            ];
        }

        return [
            'total_reviews' => $totalReviews,
            'avg_completion_rate' => $avgCompletionRate,
            'avg_hours_planned' => $avgHoursPlanned,
            'avg_hours_completed' => $avgHoursCompleted,
            'participation_rate' => $participationRate,
            'users_with_reviews' => $usersWithReviews,
            'total_users' => $totalUsers,
            'weekly_trends' => $weeklyTrends,
            'by_department' => $byDepartment,
        ];
    }

    // ========== HELPER METHODS ==========

    protected function getTotalSpent($budgetItemIds): float
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

    public function getWeeks()
    {
        return $this->calendarService->getweeks($this->year);
    }

    public function getBudgets()
    {
        return $this->budgetRepository->getbudgets();
    }

    public function getDepartments()
    {
        return $this->departmentRepository->getdepartments();
    }

    public function clearDepartmentFilter()
    {
        $this->selectedDepartmentId = null;
    }

    public function render()
    {
        return view('livewire.admin.trackers.organisationdashboard', [
            'overallMetrics' => $this->getOverallMetrics(),
            'departmentBreakdown' => $this->getDepartmentBreakdown(),
            'departmentComparison' => $this->getDepartmentComparison(),
            'budgetDistribution' => $this->getBudgetDistribution(),
            'timeBasedTrends' => $this->getTimeBasedTrends(),
            'topPerformers' => $this->getTopPerformers(),
            'organizationHealth' => $this->getOrganizationHealthScorecard(),
            'workplanProgress' => $this->getOrganizationWorkplanProgress(),
            'issueResolution' => $this->getOrganizationIssueResolution(),
            'budgetSpendingTrends' => $this->getBudgetSpendingTrends(),
            'riskIndicators' => $this->getRiskIndicators(),
            'comparativeAnalysis' => $this->getComparativeAnalysis(),
            'workloadDistribution' => $this->getWorkloadDistribution(),
            'productivityMetrics' => $this->getProductivityMetrics(),
            'strategicGoals' => $this->getStrategicGoalsTracking(),
            'recentActivity' => $this->getRecentActivity(),
            'performanceHeatmap' => $this->getDepartmentPerformanceHeatmap(),
            'budgetForecast' => $this->getBudgetForecast(),
            'taskApprovalMetrics' => $this->getTaskApprovalMetrics(),
            'supervisorApprovalRates' => $this->getSupervisorApprovalRates(),
            'weeklyTopApprovers' => $this->getWeeklyTopApprovers(),
            'taskHoursProductivity' => $this->getTaskHoursProductivity(),
            'weeklyReviewSummary' => $this->getWeeklyReviewSummary(),
            'weeks' => $this->getWeeks(),
            'budgets' => $this->getBudgets(),
            'departments' => $this->getDepartments(),
        ]);
    }
}
