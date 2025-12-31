<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\iworkflowInterface;
use App\Interfaces\services\itsallowanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class TsAllowancelist extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $search;

    public $year;

    public $departmentId = 0;

    public $user;

    public $selectedTab = 'all-tab';

    // Track expanded stages and allowances
    public $expandedStages = [];

    public $expandedAllowances = [];

    // Tab tracking for each allowance
    public $selectedTabs = [];

    // Decision Modal
    public $decisionmodal = false;

    // Bulk Approval Modal
    public $bulkapprovalmodal = false;

    public $selectedForBulk = [];

    public $bulkComment = '';

    public $bulkApprovalCode = '';

    public $bulkStepOrder;

    public $bulkStepName;

    public $bulkStatus;

    // Selected allowance
    public $selectedAllowanceUuid;

    public $selectedAllowanceId;

    // Current workflow step info
    public $currentStepName;

    public $currentStepOrder;

    // Decision form fields
    public $decision;

    public $comment;

    public $approvalcode;

    protected $tsallowanceService;

    protected $workflowRepository;

    protected $authrepo;

    public function boot(itsallowanceService $tsallowanceService, iworkflowInterface $workflowRepository, iauthInterface $authrepo)
    {
        $this->tsallowanceService = $tsallowanceService;
        $this->workflowRepository = $workflowRepository;
        $this->authrepo = $authrepo;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'T&S Allowance Approvals'],
        ];
        $this->year = date('Y');
        $this->search = '';
        $this->user = Auth::user();

        // Get user's department ID for HOD filtering
        try {
            $this->departmentId = $this->tsallowanceService->getuserdepartmentid($this->user->email);
        } catch (\Exception $e) {
            $this->departmentId = 0;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getallowances()
    {
        // If user has permission to view all, return all allowances (as collection)
        if ($this->user->can('tsa.view.all')) {
            return $this->tsallowanceService->getallallowances($this->year);
        }

        // User doesn't have view.all - need to filter by workflow step
        // Get workflow to check which steps user can access
        $workflow = $this->getworkflow();
        if (! $workflow) {
            return collect();
        }

        $filteredAllowances = collect();

        foreach ($workflow->workflowparameters as $workflowParameter) {
            // Check if user has permission for this workflow step
            if (! $this->user->can($workflowParameter->permission->name)) {
                continue;
            }

            // If this is the HOD step (first step, order 1), filter by department
            if ($workflowParameter->order == 1) {
                // HOD step - get only department's allowances for this status
                $deptAllowances = $this->tsallowanceService->getalldeptallowances($this->year, $this->departmentId, $this->search);
                $statusAllowances = $deptAllowances->where('status', $workflowParameter->status);
                $filteredAllowances = $filteredAllowances->merge($statusAllowances);
            } else {
                // Any step after HOD (order > 1) - user has permission, so show all for this status
                $allAllowances = $this->tsallowanceService->getallallowances($this->year);
                $statusAllowances = $allAllowances->where('status', $workflowParameter->status);
                $filteredAllowances = $filteredAllowances->merge($statusAllowances);
            }
        }

        return $filteredAllowances;
    }

    public function getallowancesbyworkflowparameter()
    {
        return $this->tsallowanceService->getallowancesbyworkflowparameter($this->year);
    }

    public function getworkflow()
    {
        $name = config('workflow.ts_allowances');

        return $this->workflowRepository->getworkflowbystatus($name);
    }

    public function toggleStage($status)
    {
        if (in_array($status, $this->expandedStages)) {
            $this->expandedStages = array_diff($this->expandedStages, [$status]);
        } else {
            $this->expandedStages[] = $status;
        }
    }

    public function isStageExpanded($status)
    {
        return in_array($status, $this->expandedStages);
    }

    public function toggleAllowance($uuid)
    {
        if (in_array($uuid, $this->expandedAllowances)) {
            $this->expandedAllowances = array_diff($this->expandedAllowances, [$uuid]);
        } else {
            $this->expandedAllowances[] = $uuid;
        }
    }

    public function isAllowanceExpanded($uuid)
    {
        return in_array($uuid, $this->expandedAllowances);
    }

    public function getAllowanceByUuid($uuid)
    {
        return $this->tsallowanceService->getallowancebyuuid($uuid);
    }

    public function selectAllowance($uuid)
    {
        $this->selectedAllowanceUuid = $uuid;
        $allowance = $this->getAllowanceByUuid($uuid);
        $this->selectedAllowanceId = $allowance?->id;
    }

    // Decision Modal Methods
    public function openDecisionModal($uuid)
    {
        $this->selectAllowance($uuid);

        // Get the allowance and determine current workflow step
        $allowance = $this->getAllowanceByUuid($uuid);
        if ($allowance && $allowance->workflow) {
            $currentStep = $allowance->workflow->workflowparameters
                ->where('status', $allowance->status)
                ->first();

            if ($currentStep) {
                $this->currentStepName = $currentStep->name;
                $this->currentStepOrder = $currentStep->order;
            }
        }

        $this->decisionmodal = true;
    }

    public function getDecisionOptionsProperty()
    {
        // Step 1 (Supervisor/HOD): RECOMMEND / REJECT
        if ($this->currentStepOrder == 1) {
            return [
                ['id' => 'APPROVED', 'name' => 'RECOMMEND'],
                ['id' => 'REJECT', 'name' => 'REJECT'],
            ];
        }

        // Step 2 (Finance): FINANCE_VERIFIED / SEND_BACK
        if ($this->currentStepOrder == 2) {
            return [
                ['id' => 'APPROVED', 'name' => 'FINANCE VERIFIED'],
                ['id' => 'SEND_BACK', 'name' => 'SEND BACK FOR CORRECTIONS'],
            ];
        }

        // Step 3+ (CEO and others): APPROVE / REJECT
        return [
            ['id' => 'APPROVED', 'name' => 'APPROVE'],
            ['id' => 'REJECT', 'name' => 'REJECT'],
        ];
    }

    public function savedecision()
    {
        $this->validate([
            'decision' => 'required',
            'comment' => 'required_if:decision,REJECT|required_if:decision,SEND_BACK',
            'approvalcode' => 'required',
        ]);

        $checkcode = $this->authrepo->checkapprovalcode($this->approvalcode);
        if ($checkcode['status'] == 'success') {
            if ($this->decision == 'APPROVED') {
                $response = $this->tsallowanceService->approve($this->selectedAllowanceId, [
                    'comment' => $this->comment ?? '',
                    'authorization_code' => $this->approvalcode,
                ]);
            } elseif ($this->decision == 'SEND_BACK') {
                // Send back to applicant for corrections
                $response = $this->tsallowanceService->sendback($this->selectedAllowanceId, [
                    'comment' => $this->comment,
                    'authorization_code' => $this->approvalcode,
                ]);
            } else {
                $response = $this->tsallowanceService->reject($this->selectedAllowanceId, [
                    'comment' => $this->comment,
                    'authorization_code' => $this->approvalcode,
                ]);
            }

            if ($response['status'] == 'success') {
                $this->success($response['message']);
                $this->reset(['decision', 'comment', 'approvalcode', 'selectedAllowanceUuid', 'selectedAllowanceId', 'currentStepName', 'currentStepOrder']);
                $this->decisionmodal = false;
            } else {
                $this->error($response['message']);
            }
        } else {
            $this->error($checkcode['message']);
        }
    }

    // Bulk Approval Methods
    public function toggleBulkSelection($uuid)
    {
        if (in_array($uuid, $this->selectedForBulk)) {
            $this->selectedForBulk = array_values(array_diff($this->selectedForBulk, [$uuid]));
        } else {
            $this->selectedForBulk[] = $uuid;
        }
    }

    public function selectAllForBulk($status)
    {
        $allowances = $this->getallowances()->where('status', $status);
        $this->selectedForBulk = $allowances->pluck('uuid')->toArray();
    }

    public function clearBulkSelection()
    {
        $this->selectedForBulk = [];
    }

    public function openBulkApprovalModal($status)
    {
        if (empty($this->selectedForBulk)) {
            $this->error('Please select at least one allowance to approve');

            return;
        }

        // Determine the step info based on status
        $workflow = $this->getworkflow();
        if ($workflow) {
            $step = $workflow->workflowparameters->where('status', $status)->first();
            if ($step) {
                $this->bulkStepOrder = $step->order;
                $this->bulkStepName = $step->name;
                $this->bulkStatus = $status;
            }
        }

        $this->bulkapprovalmodal = true;
    }

    public function getBulkDecisionLabelProperty()
    {
        if ($this->bulkStepOrder == 1) {
            return 'RECOMMEND';
        }
        if ($this->bulkStepOrder == 2) {
            return 'FINANCE VERIFIED';
        }

        return 'APPROVE';
    }

    public function executeBulkApproval()
    {
        $this->validate([
            'bulkApprovalCode' => 'required',
        ]);

        $checkcode = $this->authrepo->checkapprovalcode($this->bulkApprovalCode);
        if ($checkcode['status'] != 'success') {
            $this->error($checkcode['message']);

            return;
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($this->selectedForBulk as $uuid) {
            $allowance = $this->getAllowanceByUuid($uuid);
            if (! $allowance) {
                $failCount++;
                $errors[] = "Allowance {$uuid} not found";

                continue;
            }

            $response = $this->tsallowanceService->approve($allowance->id, [
                'comment' => $this->bulkComment ?? 'Bulk approved',
                'authorization_code' => $this->bulkApprovalCode,
            ]);

            if ($response['status'] == 'success') {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "{$allowance->application_number}: {$response['message']}";
            }
        }

        if ($successCount > 0) {
            $this->success("{$successCount} allowance(s) approved successfully");
        }

        if ($failCount > 0) {
            $this->error("{$failCount} allowance(s) failed: ".implode(', ', array_slice($errors, 0, 3)));
        }

        $this->reset(['selectedForBulk', 'bulkComment', 'bulkApprovalCode', 'bulkStepOrder', 'bulkStepName', 'bulkStatus']);
        $this->bulkapprovalmodal = false;
    }

    public function headers(): array
    {
        return [
            ['key' => 'application_number', 'label' => 'Application #'],
            ['key' => 'full_name', 'label' => 'Applicant'],
            ['key' => 'department', 'label' => 'Department'],
            ['key' => 'trip_start_date', 'label' => 'Start Date'],
            ['key' => 'trip_end_date', 'label' => 'End Date'],
            ['key' => 'balance_due', 'label' => 'Amount'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.ts-allowancelist', [
            'breadcrumbs' => $this->breadcrumbs,
            'allowances' => $this->getallowances(),
            'workflow' => $this->getworkflow(),
            'workflowparameters' => $this->getallowancesbyworkflowparameter(),
            'headers' => $this->headers(),
        ]);
    }
}
