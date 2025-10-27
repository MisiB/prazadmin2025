<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iissuelogInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class IssueTracker extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $filterStatus = '';

    public $filterPriority = '';

    public $filterDepartment = '';

    public $filterGroup = '';

    public $filterType = '';

    public $search = '';

    public $groupBy = 'status';

    public $showTatModal = false;

    public $selectedDepartmentId = null;

    protected $issueRepository;

    protected $departmentRepository;

    public function boot(iissuelogInterface $issueRepository, idepartmentInterface $departmentRepository)
    {
        $this->issueRepository = $issueRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Issue Tracker Dashboard'],
        ];
    }

    public function getFilteredIssues()
    {
        $issues = $this->issueRepository->getissueswithmetrics();

        if ($this->search) {
            $issues = $issues->filter(function ($issue) {
                return str_contains(strtolower($issue->title), strtolower($this->search)) ||
                       str_contains(strtolower($issue->ticketnumber), strtolower($this->search)) ||
                       str_contains(strtolower($issue->description ?? ''), strtolower($this->search));
            });
        }

        if ($this->filterStatus) {
            $issues = $issues->where('status', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $issues = $issues->where('priority', $this->filterPriority);
        }

        if ($this->filterDepartment) {
            $issues = $issues->where('department_id', $this->filterDepartment);
        }

        if ($this->filterGroup) {
            $issues = $issues->where('issuegroup_id', $this->filterGroup);
        }

        if ($this->filterType) {
            $issues = $issues->where('issuetype_id', $this->filterType);
        }

        return $issues;
    }

    public function getGroupedIssues()
    {
        $issues = $this->getFilteredIssues();

        return match ($this->groupBy) {
            'status' => $issues->groupBy('status'),
            'group' => $issues->groupBy('issuegroup_id'),
            'type' => $issues->groupBy('issuetype_id'),
            'department' => $issues->groupBy('department_id'),
            default => collect(['all' => $issues]),
        };
    }

    public function getOverallStats()
    {
        $issues = $this->issueRepository->getissueswithmetrics();

        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
            'high_priority' => $issues->where('priority', 'High')->count(),
            'unassigned' => $issues->whereNull('assigned_to')->count(),
        ];
    }

    public function getIssuesByGroup()
    {
        $issues = $this->issueRepository->getissueswithmetrics();

        return $issues->groupBy('issuegroup_id')->map(function ($groupIssues) {
            return [
                'count' => $groupIssues->count(),
                'name' => $groupIssues->first()->issuegroup->name ?? 'Unknown',
                'open' => $groupIssues->where('status', 'open')->count(),
                'resolved' => $groupIssues->whereIn('status', ['resolved', 'closed'])->count(),
            ];
        });
    }

    public function getIssuesByType()
    {
        $issues = $this->issueRepository->getissueswithmetrics();

        return $issues->groupBy('issuetype_id')->map(function ($typeIssues) {
            return [
                'count' => $typeIssues->count(),
                'name' => $typeIssues->first()->issuetype->name ?? 'Unknown',
                'department' => $typeIssues->first()->issuetype->department->name ?? 'N/A',
            ];
        });
    }

    public function getDepartmentStats()
    {
        $issues = $this->issueRepository->getissueswithmetrics();
        $departments = $this->departmentRepository->getdepartments();

        return $departments->map(function ($dept) use ($issues) {
            $deptIssues = $issues->where('department_id', $dept->id);
            $tatData = $this->issueRepository->getdepartmentturnaroundtime($dept->id);

            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'total' => $deptIssues->count(),
                'open' => $deptIssues->where('status', 'open')->count(),
                'in_progress' => $deptIssues->where('status', 'in_progress')->count(),
                'resolved' => $deptIssues->whereIn('status', ['resolved', 'closed'])->count(),
                'avg_tat_hours' => $tatData['avg_hours'],
                'avg_tat_days' => round($tatData['avg_hours'] / 24, 1),
            ];
        })->sortByDesc('total');
    }

    public function getUserPerformance()
    {
        $issues = $this->issueRepository->getissueswithmetrics();

        // Get all users who have been assigned issues
        $userIssues = $issues->whereNotNull('assigned_to')->groupBy('assigned_to');

        return $userIssues->map(function ($userIssues, $userId) {
            $user = $userIssues->first()->assignedto;
            $tatData = $this->issueRepository->getuserturnaroundtime($userId);

            return [
                'user_id' => $userId,
                'name' => $user ? $user->name.' '.$user->surname : 'Unknown',
                'department' => $userIssues->first()->department->name ?? 'N/A',
                'total_assigned' => $userIssues->count(),
                'resolved' => $userIssues->whereIn('status', ['resolved', 'closed'])->count(),
                'pending' => $userIssues->whereIn('status', ['open', 'in_progress'])->count(),
                'avg_tat_hours' => $tatData['avg_hours'],
                'avg_tat_days' => round($tatData['avg_hours'] / 24, 1),
                'resolution_rate' => $userIssues->count() > 0
                    ? round(($userIssues->whereIn('status', ['resolved', 'closed'])->count() / $userIssues->count()) * 100, 1)
                    : 0,
            ];
        })->sortByDesc('total_assigned')->take(20);
    }

    public function openTatModal($departmentId = null)
    {
        $this->selectedDepartmentId = $departmentId;
        $this->showTatModal = true;
    }

    public function closeTatModal()
    {
        $this->showTatModal = false;
        $this->selectedDepartmentId = null;
    }

    public function render()
    {
        return view('livewire.admin.issue-tracker', [
            'overallStats' => $this->getOverallStats(),
            'issuesByGroup' => $this->getIssuesByGroup(),
            'issuesByType' => $this->getIssuesByType(),
            'departmentStats' => $this->getDepartmentStats(),
            'userPerformance' => $this->getUserPerformance(),
            'groupedIssues' => $this->getGroupedIssues(),
            'departments' => $this->departmentRepository->getdepartments(),
            'issueGroups' => $this->issueRepository->getissuegroups(),
            'issueTypes' => $this->issueRepository->getissuetypes(),
        ]);
    }
}
