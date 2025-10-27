<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Models\Departmentuser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class DepartmentalIssues extends Component
{
    use Toast, WithFileUploads;

    public $breadcrumbs = [];

    public $userDepartment = null;

    public $isPrimaryUser = false;

    public $filterStatus = '';

    public $filterPriority = '';

    public $filterType = '';

    public $search = '';

    public $groupBy = 'status';

    // Assignment modal
    public $showAssignModal = false;

    public $assigningIssueId = null;

    public $selectedUser = null;

    public $isReassigning = false;

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
            ['label' => 'Departmental Issues'],
        ];

        // Get user's department
        $deptUser = Departmentuser::where('user_id', Auth::id())->first();

        if ($deptUser) {
            $this->userDepartment = $deptUser->department;
            $this->isPrimaryUser = (bool) $deptUser->isprimary;
        }
    }

    public function updateStatus($id, $status)
    {
        $response = $this->issueRepository->updateissuestatus($id, $status);

        if ($response['status'] === 'success') {
            if ($status === 'resolved') {
                $this->success($response['message'].' Email notification sent to the user.');
            } else {
                $this->success($response['message']);
            }
        } else {
            $this->error($response['message']);
        }
    }

    public function openAssignModal($issueId, $isReassigning = false)
    {
        $this->assigningIssueId = $issueId;
        $this->isReassigning = $isReassigning;

        // Pre-fill current assignment if reassigning
        if ($isReassigning) {
            $issue = $this->issueRepository->getissuelog($issueId);
            $this->selectedUser = $issue->assigned_to;
        } else {
            $this->selectedUser = null;
        }

        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assigningIssueId = null;
        $this->selectedUser = null;
        $this->isReassigning = false;
    }

    public function assignIssue()
    {
        $this->validate([
            'selectedUser' => 'required|exists:users,id',
        ]);

        $response = $this->issueRepository->assignissue(
            $this->assigningIssueId,
            $this->selectedUser,
            $this->userDepartment->id
        );

        if ($response['status'] === 'success') {
            if ($this->isReassigning) {
                $this->success('Issue reassigned successfully');
            } else {
                $this->success($response['message']);
            }
            $this->closeAssignModal();
        } else {
            $this->error($response['message']);
        }
    }

    public function reassignIssue($issueId)
    {
        $this->openAssignModal($issueId, true);
    }

    public function getDepartmentIssues()
    {
        if (! $this->userDepartment) {
            return collect();
        }

        $query = $this->issueRepository->getdepartmentissues($this->userDepartment->id);

        if ($this->search) {
            $query = $query->filter(function ($issue) {
                return str_contains(strtolower($issue->title), strtolower($this->search)) ||
                       str_contains(strtolower($issue->ticketnumber), strtolower($this->search)) ||
                       str_contains(strtolower($issue->description), strtolower($this->search));
            });
        }

        if ($this->filterStatus) {
            $query = $query->where('status', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query = $query->where('priority', $this->filterPriority);
        }

        if ($this->filterType) {
            $query = $query->where('issuetype_id', $this->filterType);
        }

        return $query;
    }

    public function getGroupedIssues()
    {
        $issues = $this->getDepartmentIssues();

        if ($this->groupBy === 'status') {
            return $issues->groupBy('status');
        }

        if ($this->groupBy === 'type') {
            return $issues->groupBy('issuetype_id');
        }

        return collect(['all' => $issues]);
    }

    public function getStatusCounts()
    {
        $issues = $this->getDepartmentIssues();

        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
        ];
    }

    public function getDepartmentUsers()
    {
        if (! $this->userDepartment) {
            return collect();
        }

        return $this->issueRepository->getdepartmentusers($this->userDepartment->id);
    }

    public function render()
    {
        return view('livewire.admin.departmental-issues', [
            'groupedIssues' => $this->getGroupedIssues(),
            'statusCounts' => $this->getStatusCounts(),
            'departmentUsers' => $this->getDepartmentUsers(),
            'issueTypes' => $this->issueRepository->getissuetypes(),
        ]);
    }
}
