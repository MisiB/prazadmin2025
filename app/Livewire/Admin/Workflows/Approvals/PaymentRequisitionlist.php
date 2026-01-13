<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Interfaces\repositories\iworkflowInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class PaymentRequisitionlist extends Component
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
            ['label' => 'Payment Requisition approvals'],
        ];

        $this->user = Auth::user();

        // Get user's department ID for supervisor filtering
        try {
            $this->departmentId = $this->user->department->department_id ?? 0;
        } catch (\Exception $e) {
            $this->departmentId = 0;
        }
    }

    public function boot(ipaymentrequisitionInterface $repository, iworkflowInterface $workflowRepository, iauthInterface $authrepo)
    {
        $this->repository = $repository;
        $this->workflowRepository = $workflowRepository;
        $this->authrepo = $authrepo;
    }

    public function getpaymentrequisitionlist()
    {
        $result = $this->repository->getpaymentrequisitions($this->year);

        return $result instanceof \Illuminate\Pagination\LengthAwarePaginator ? $result->getCollection() : collect($result);
    }

    public function getawaitingrecommendation()
    {
        // Get all Submitted requisitions (awaiting HOD recommendation)
        $result = $this->repository->getpaymentrequisitionbystatus($this->year, 'Submitted');
        $requisitions = $result instanceof \Illuminate\Pagination\LengthAwarePaginator ? $result->getCollection() : collect($result);

        // If user doesn't have permission to view all, filter by department (supervisor can only see their department's requisitions)
        if (! $this->user->can('payment.requisition.view.all') && $this->departmentId > 0) {
            $requisitions = $requisitions->where('department_id', $this->departmentId);
        }

        return $requisitions;
    }

    public function getAllRequisitions()
    {
        $allRequisitions = $this->getpaymentrequisitionlist();
        $awaitingRecommendation = $this->getawaitingrecommendation();

        return $allRequisitions->merge($awaitingRecommendation);
    }

    public function getworkflowbystatus()
    {
        $name = config('workflow.payment_requisitions');

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
        return $this->repository->getpaymentrequisitionbyuuid($uuid);
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
        if ($requisition->status == 'Submitted') {
            $this->currentStepName = 'HOD Recommendation';
        } elseif ($requisition->status == 'HOD_RECOMMENDED') {
            $this->currentStepName = 'Admin Review';
        } elseif ($requisition->status == 'ADMIN_REVIEWED') {
            $this->currentStepName = 'Admin Recommend';
        } elseif ($requisition->status == 'ADMIN_RECOMMENDED') {
            $this->currentStepName = 'Final Approval';
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

        if ($requisition->status == 'Submitted') {
            return [['id' => 'RECOMMEND', 'name' => 'RECOMMEND'], ['id' => 'REJECT', 'name' => 'REJECT']];
        } elseif ($requisition->status == 'ADMIN_REVIEWED') {
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
            $response = null;

            if ($requisition->status == 'Submitted') {
                $response = $this->repository->recommendhod($this->selectedRequisitionId, ['decision' => $this->decision, 'comment' => $this->comment ?? '']);
            } elseif ($requisition->status == 'HOD_RECOMMENDED') {
                $response = $this->repository->reviewadmin($this->selectedRequisitionId, ['decision' => $this->decision, 'comment' => $this->comment ?? '']);
            } elseif ($requisition->status == 'ADMIN_REVIEWED') {
                $response = $this->repository->recommendadmin($this->selectedRequisitionId, ['decision' => $this->decision, 'comment' => $this->comment ?? '']);
            } elseif ($requisition->status == 'ADMIN_RECOMMENDED') {
                $response = $this->repository->approvefinal($this->selectedRequisitionId, ['decision' => $this->decision, 'comment' => $this->comment ?? '']);
            }

            if ($response && $response['status'] == 'success') {
                $this->success($response['message']);
                $this->reset(['decision', 'comment', 'approvalcode', 'selectedRequisitionUuid', 'selectedRequisitionId']);
                $this->decisionmodal = false;
            } else {
                $this->error($response['message'] ?? 'An error occurred');
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
        if ($status == 'Submitted') {
            $this->bulkStepOrder = 0;
            $this->bulkStepName = 'HOD Recommendation';
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
            $response = null;
            if ($requisition->status == 'Submitted') {
                $response = $this->repository->recommendhod($requisition->id, [
                    'decision' => 'RECOMMEND',
                    'comment' => $this->bulkComment ?? 'Bulk approved',
                ]);
            } elseif ($requisition->status == 'HOD_RECOMMENDED') {
                $response = $this->repository->reviewadmin($requisition->id, [
                    'decision' => 'APPROVED',
                    'comment' => $this->bulkComment ?? 'Bulk approved',
                ]);
            } elseif ($requisition->status == 'ADMIN_REVIEWED') {
                $response = $this->repository->recommendadmin($requisition->id, [
                    'decision' => 'RECOMMEND',
                    'comment' => $this->bulkComment ?? 'Bulk approved',
                ]);
            } elseif ($requisition->status == 'ADMIN_RECOMMENDED') {
                $response = $this->repository->approvefinal($requisition->id, [
                    'decision' => 'APPROVED',
                    'comment' => $this->bulkComment ?? 'Bulk approved',
                ]);
            }

            if ($response && $response['status'] == 'success') {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "{$requisition->reference_number}: ".($response['message'] ?? 'Failed');
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

        return view('livewire.admin.workflows.approvals.payment-requisitionlist', [
            'requisitions' => $allRequisitions,
            'awaitingrecommendation' => $this->getawaitingrecommendation(),
            'workflow' => $workflow,
        ]);
    }
}
