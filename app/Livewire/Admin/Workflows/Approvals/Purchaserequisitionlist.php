<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use App\Interfaces\repositories\iworkflowInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Purchaserequisitionlist extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $year;

    public $selectedTabs = [];

    protected $repository;

    protected $workflowRepository;

    protected $authrepo;

    public $user;

    public $departmentId = 0;

    // Expanded stages tracking
    public $expandedStages = [];

    // Expanded requisitions tracking
    public $expandedRequisitions = [];

    // Decision modal
    public $decisionmodal = false;

    public $selectedRequisitionUuid = null;

    public $selectedRequisitionId = null;

    public $decision;

    public $comment;

    public $approvalcode;

    // Bulk Approval Modal
    public $bulkapprovalmodal = false;

    public $selectedForBulk = [];

    public $bulkComment = '';

    public $bulkApprovalCode = '';

    public $bulkStepOrder;

    public $bulkStepName;

    public $bulkStatus;

    public $currentStepOrder;

    public function mount()
    {
        $this->year = date('Y');
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Purchase Requisition approvals'],
        ];

        $this->user = Auth::user();

        // Get user's department ID for supervisor filtering
        try {
            $this->departmentId = $this->user->department->department_id ?? 0;
        } catch (\Exception $e) {
            $this->departmentId = 0;
        }
    }

    public function boot(ipurchaseerequisitionInterface $repository, iworkflowInterface $workflowRepository, iauthInterface $authrepo)
    {
        $this->repository = $repository;
        $this->workflowRepository = $workflowRepository;
        $this->authrepo = $authrepo;
    }

    public function getpurchaserequisitionlist()
    {
        $result = $this->repository->getpurchaseerequisitions($this->year);

        return $result instanceof \Illuminate\Pagination\LengthAwarePaginator ? $result->getCollection() : collect($result);
    }

    public function getawaitingrecommendation()
    {
        // Get all AWAITING_RECOMMENDATION requisitions
        $result = $this->repository->getpurchaseerequisitionbystatus($this->year, 'AWAITING_RECOMMENDATION');
        $requisitions = $result instanceof \Illuminate\Pagination\LengthAwarePaginator ? $result->getCollection() : collect($result);

        // If user doesn't have permission to view all, filter by department (supervisor can only see their department's requisitions)
        if (! $this->user->can('purchaserequisition.view.all') && $this->departmentId > 0) {
            $requisitions = $requisitions->where('department_id', $this->departmentId);
        }

        return $requisitions;
    }

    public function getAllRequisitions()
    {
        $allRequisitions = $this->getpurchaserequisitionlist();
        $awaitingRecommendation = $this->getawaitingrecommendation();

        return $allRequisitions->merge($awaitingRecommendation);
    }

    public function getworkflowbystatus()
    {
        $name = config('workflow.purchase_requisitions');

        return $this->workflowRepository->getworkflowbystatus($name);
    }

    public function toggleStage($status)
    {
        if (isset($this->expandedStages[$status])) {
            unset($this->expandedStages[$status]);
            // Also collapse all requisitions in this stage
            foreach ($this->expandedRequisitions as $uuid => $value) {
                $requisition = $this->getAllRequisitions()->firstWhere('uuid', $uuid);
                if ($requisition && $requisition->status === $status) {
                    unset($this->expandedRequisitions[$uuid]);
                }
            }
        } else {
            $this->expandedStages[$status] = true;
        }
    }

    public function isStageExpanded($status)
    {
        return isset($this->expandedStages[$status]);
    }

    public function toggleRequisition($uuid)
    {
        if (isset($this->expandedRequisitions[$uuid])) {
            unset($this->expandedRequisitions[$uuid]);
        } else {
            $this->expandedRequisitions[$uuid] = true;
        }
    }

    public function isRequisitionExpanded($uuid)
    {
        return isset($this->expandedRequisitions[$uuid]);
    }

    public function getRequisitionByUuid($uuid)
    {
        return $this->repository->getpurchaseerequisitionbyuuid($uuid);
    }

    public $currentStepName = 'Make Decision';

    public function openDecisionModal($uuid)
    {
        $requisition = $this->getRequisitionByUuid($uuid);
        $this->selectedRequisitionUuid = $uuid;
        $this->selectedRequisitionId = $requisition->id;
        $this->decision = null;
        $this->comment = null;
        $this->approvalcode = null;

        // Set modal title based on status
        if ($requisition->status == 'AWAITING_RECOMMENDATION') {
            $this->currentStepName = 'Supervisor/HOD Recommendation';
        } else {
            $workflowParameter = $requisition->workflow->workflowparameters->where('status', $requisition->status)->first();
            $this->currentStepName = $workflowParameter->name ?? 'Make Decision';
        }

        $this->decisionmodal = true;
    }

    public function getDecisionOptionsProperty()
    {
        if (! $this->selectedRequisitionUuid) {
            return [['id' => 'APPROVED', 'name' => 'APPROVED'], ['id' => 'REJECTED', 'name' => 'REJECTED']];
        }

        $requisition = $this->getRequisitionByUuid($this->selectedRequisitionUuid);

        if ($requisition->status == 'AWAITING_RECOMMENDATION') {
            return [['id' => 'RECOMMEND', 'name' => 'RECOMMEND'], ['id' => 'REJECT', 'name' => 'REJECT']];
        }

        return [['id' => 'APPROVED', 'name' => 'APPROVED'], ['id' => 'REJECTED', 'name' => 'REJECTED']];
    }

    public function savedecision()
    {
        $this->validate([
            'decision' => 'required',
            'comment' => 'nullable',
            'approvalcode' => 'required',
        ]);
        $checkcode = $this->authrepo->checkapprovalcode($this->approvalcode);
        if ($checkcode['status'] == 'success') {
            $requisition = $this->getRequisitionByUuid($this->selectedRequisitionUuid);
            if ($requisition->status == 'AWAITING_RECOMMENDATION') {
                $response = $this->repository->recommend($this->selectedRequisitionId, ['decision' => $this->decision, 'comment' => $this->comment ?? '']);
            } else {
                $response = $this->repository->makedecision($this->selectedRequisitionId, ['decision' => $this->decision, 'comment' => $this->comment ?? '']);
            }
            if ($response['status'] == 'success') {
                $this->success($response['message']);
                $this->reset(['decision', 'comment', 'approvalcode', 'selectedRequisitionUuid', 'selectedRequisitionId']);
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
        $allRequisitions = $this->getAllRequisitions();
        $requisitions = $allRequisitions->where('status', $status);
        $this->selectedForBulk = $requisitions->pluck('uuid')->toArray();
    }

    public function clearBulkSelection()
    {
        $this->selectedForBulk = [];
    }

    public function openBulkApprovalModal($status)
    {
        if (empty($this->selectedForBulk)) {
            $this->error('Please select at least one requisition to approve');

            return;
        }

        // Determine the step info based on status
        if ($status == 'AWAITING_RECOMMENDATION') {
            $this->bulkStepOrder = 0;
            $this->bulkStepName = 'Supervisor/HOD Recommendation';
            $this->bulkStatus = $status;
        } else {
            $workflow = $this->getworkflowbystatus();
            if ($workflow) {
                $step = $workflow->workflowparameters->where('status', $status)->first();
                if ($step) {
                    $this->bulkStepOrder = $step->order;
                    $this->bulkStepName = $step->name;
                    $this->bulkStatus = $status;
                }
            }
        }

        $this->bulkapprovalmodal = true;
    }

    public function getBulkDecisionLabelProperty()
    {
        if ($this->bulkStepOrder == 0) {
            return 'RECOMMEND';
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
            $requisition = $this->getRequisitionByUuid($uuid);
            if (! $requisition) {
                $failCount++;
                $errors[] = "Requisition {$uuid} not found";

                continue;
            }

            // Determine which method to call based on status
            if ($requisition->status == 'AWAITING_RECOMMENDATION') {
                $response = $this->repository->recommend($requisition->id, [
                    'decision' => 'RECOMMEND',
                    'comment' => $this->bulkComment ?? 'Bulk approved',
                ]);
            } else {
                $response = $this->repository->makedecision($requisition->id, [
                    'decision' => 'APPROVED',
                    'comment' => $this->bulkComment ?? 'Bulk approved',
                ]);
            }

            if ($response['status'] == 'success') {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "{$requisition->prnumber}: {$response['message']}";
            }
        }

        if ($successCount > 0) {
            $this->success("{$successCount} requisition(s) approved successfully");
        }

        if ($failCount > 0) {
            $this->error("{$failCount} requisition(s) failed: ".implode(', ', array_slice($errors, 0, 3)));
        }

        $this->reset(['selectedForBulk', 'bulkComment', 'bulkApprovalCode', 'bulkStepOrder', 'bulkStepName', 'bulkStatus']);
        $this->bulkapprovalmodal = false;
    }

    public function render()
    {
        $allRequisitions = $this->getAllRequisitions();
        $workflow = $this->getworkflowbystatus();

        return view('livewire.admin.workflows.approvals.purchaserequisitionlist', [
            'requisitions' => $allRequisitions,
            'awaitingrecommendation' => $this->getawaitingrecommendation(),
            'workflow' => $workflow,
        ]);
    }
}
