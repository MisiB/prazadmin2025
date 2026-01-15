<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Interfaces\repositories\istaffwelfareloanInterface;
use App\Interfaces\repositories\itsallowanceInterface;
use App\Interfaces\repositories\iworkflowInterface;
use App\Interfaces\services\ipaymentvoucherService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class PaymentVoucherlist extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $year;

    public $selectedTabs = [];

    public $expandedStages = [];

    public $expandedVouchers = [];

    public $expandedItems = [];

    public $decisionmodal = false;

    public $selectedVoucherUuid = null;

    public $selectedVoucherId = null;

    public $decision;

    public $comment;

    public $approvalcode;

    public $viewItemModal = false;

    public $viewedItemDetails = null;

    public $viewedItemSourceType = null;

    public $viewedItemLineId = null;

    public $bulkapprovalmodal = false;

    public $selectedForBulk = [];

    public $bulkComment = '';

    public $bulkApprovalCode = '';

    public $bulkStepOrder;

    public $bulkStepName;

    public $bulkStatus;

    protected $paymentvoucherService;

    protected $authrepo;

    protected $workflowRepository;

    protected $paymentrequisitionrepo;

    protected $tsallowancerepo;

    protected $staffwelfareloanrepo;

    public $user;

    public function mount()
    {
        $this->year = date('Y');
        $this->user = Auth::user();
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Voucher Approvals'],
        ];
    }

    public function boot(
        ipaymentvoucherService $paymentvoucherService,
        iauthInterface $authrepo,
        iworkflowInterface $workflowRepository,
        ipaymentrequisitionInterface $paymentrequisitionrepo,
        itsallowanceInterface $tsallowancerepo,
        istaffwelfareloanInterface $staffwelfareloanrepo
    ) {
        $this->paymentvoucherService = $paymentvoucherService;
        $this->authrepo = $authrepo;
        $this->workflowRepository = $workflowRepository;
        $this->paymentrequisitionrepo = $paymentrequisitionrepo;
        $this->tsallowancerepo = $tsallowancerepo;
        $this->staffwelfareloanrepo = $staffwelfareloanrepo;
    }

    public function toggleStage($status)
    {
        if (isset($this->expandedStages[$status])) {
            unset($this->expandedStages[$status]);
            // Also collapse all vouchers in this stage
            foreach ($this->expandedVouchers as $uuid => $value) {
                $voucher = $this->getvouchers()->firstWhere('uuid', $uuid);
                if ($voucher && $voucher->status === $status) {
                    unset($this->expandedVouchers[$uuid]);
                }
            }
        } else {
            $this->expandedStages[$status] = true;
        }
    }

    public function toggleVoucher($uuid)
    {
        if (isset($this->expandedVouchers[$uuid])) {
            unset($this->expandedVouchers[$uuid]);
        } else {
            $this->expandedVouchers[$uuid] = true;
        }
    }

    public function isVoucherExpanded($uuid)
    {
        return isset($this->expandedVouchers[$uuid]);
    }

    public function toggleItem($itemId)
    {
        if (isset($this->expandedItems[$itemId])) {
            unset($this->expandedItems[$itemId]);
        } else {
            $this->expandedItems[$itemId] = true;
        }
    }

    public function isItemExpanded($itemId)
    {
        return isset($this->expandedItems[$itemId]);
    }

    public function getSourceRecord($sourceType, $sourceId)
    {
        return match ($sourceType) {
            'PAYMENT_REQUISITION' => \App\Models\PaymentRequisition::with(['documents', 'approvals.user', 'lineItems', 'department', 'budgetLineItem', 'currency', 'createdBy'])->find($sourceId),
            'TNS' => \App\Models\TsAllowance::with(['department', 'applicant', 'currency'])->find($sourceId),
            'STAFF_WELFARE' => \App\Models\StaffWelfareLoan::with(['department', 'applicant'])->find($sourceId),
            default => null,
        };
    }

    public function getvouchersbystatus($status)
    {
        return $this->paymentvoucherService->getvoucherbystatus($this->year, $status);
    }

    public function getworkflow()
    {
        $name = config('workflow.payment_vouchers', 'payment_vouchers');

        return $this->workflowRepository->getworkflowbystatus($name);
    }

    public function getvouchers()
    {
        $workflow = $this->getworkflow();
        if (! $workflow) {
            return collect();
        }

        $allVouchers = $this->paymentvoucherService->getallvouchers($this->year);
        $filteredVouchers = collect();

        foreach ($workflow->workflowparameters as $workflowParameter) {
            // Check if user has permission for this workflow step
            if (! $this->user->can($workflowParameter->permission->name)) {
                continue;
            }

            // Get vouchers for this status
            $statusVouchers = $allVouchers->where('status', $workflowParameter->status);
            $filteredVouchers = $filteredVouchers->merge($statusVouchers);
        }

        // Eager load relationships to prevent N+1 queries
        $voucherIds = $filteredVouchers->pluck('id')->toArray();
        if (! empty($voucherIds)) {
            $filteredVouchers = \App\Models\PaymentVoucher::with([
                'items',
                'preparedBy',
                'verifiedBy',
                'checkedBy',
                'financeApprovedBy',
                'ceoApprovedBy',
                'auditLogs.user',
            ])->whereIn('id', $voucherIds)->get();
        }

        return $filteredVouchers;
    }

    public function getVoucherByUuid($uuid)
    {
        $voucher = $this->paymentvoucherService->getvoucherbyuuid($uuid);

        // Add currency to each item so it's accessible in @scope closures
        if ($voucher && $voucher->items) {
            $voucher->items->each(function ($item) use ($voucher) {
                $item->voucher_currency = $voucher->currency;
            });
        }

        return $voucher;
    }

    public function openDecisionModal($uuid, $id)
    {
        $this->selectedVoucherUuid = $uuid;
        $this->selectedVoucherId = $id;
        $this->reset(['decision', 'comment', 'approvalcode']);
        $this->decisionmodal = true;
    }

    public function getDecisionOptionsProperty()
    {
        $voucher = $this->getVoucherByUuid($this->selectedVoucherUuid);

        if (! $voucher) {
            return [];
        }

        // For all stages, allow APPROVE or REJECT
        return [
            ['id' => 'APPROVE', 'name' => 'APPROVE'],
            ['id' => 'REJECT', 'name' => 'REJECT'],
        ];
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
            $voucher = $this->getVoucherByUuid($this->selectedVoucherUuid);
            $response = null;

            if ($this->decision === 'REJECT') {
                $response = $this->paymentvoucherService->reject($this->selectedVoucherId, [
                    'comment' => $this->comment ?? '',
                    'authorization_code' => $this->approvalcode,
                ]);
            } else {
                // Use the dynamic approve method
                $response = $this->paymentvoucherService->approve($this->selectedVoucherId, [
                    'comment' => $this->comment ?? '',
                    'authorization_code' => $this->approvalcode,
                ]);
            }

            if ($response && $response['status'] == 'success') {
                $this->success($response['message']);
                $this->reset(['decision', 'comment', 'approvalcode', 'selectedVoucherUuid', 'selectedVoucherId']);
                $this->decisionmodal = false;
                // Force component refresh to show updated voucher list
                $this->dispatch('$refresh');
            } else {
                $this->error($response['message'] ?? 'An error occurred');
            }
        } else {
            $this->error($checkcode['message']);
        }
    }

    public function viewItemDetails($itemId)
    {
        $item = \App\Models\PaymentVoucherItem::with('paymentVoucher')->find($itemId);

        if (! $item) {
            $this->error('Item not found');

            return;
        }

        $this->viewedItemSourceType = $item->source_type;
        $this->viewedItemDetails = null;
        $this->viewedItemLineId = $item->source_line_id;

        try {
            if ($this->viewedItemSourceType === 'PAYMENT_REQUISITION') {
                $pr = \App\Models\PaymentRequisition::find($item->source_id);
                if ($pr && $pr->uuid) {
                    $this->viewedItemDetails = $this->paymentrequisitionrepo->getpaymentrequisitionbyuuid($pr->uuid);
                }
            } elseif ($this->viewedItemSourceType === 'TNS') {
                $ts = \App\Models\TsAllowance::find($item->source_id);
                if ($ts && $ts->uuid) {
                    $this->viewedItemDetails = $this->tsallowancerepo->getallowancebyuuid($ts->uuid);
                }
            } elseif ($this->viewedItemSourceType === 'STAFF_WELFARE') {
                $loan = \App\Models\StaffWelfareLoan::find($item->source_id);
                if ($loan && $loan->uuid) {
                    $this->viewedItemDetails = $this->staffwelfareloanrepo->getloanbyuuid($loan->uuid);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error loading item details: '.$e->getMessage());
            $this->error('Failed to load item details');

            return;
        }

        if (! $this->viewedItemDetails) {
            $this->error('Item details not found');

            return;
        }

        $this->viewItemModal = true;
    }

    public function closeViewItemModal()
    {
        $this->viewItemModal = false;
        $this->viewedItemDetails = null;
        $this->viewedItemSourceType = null;
        $this->viewedItemLineId = null;
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
        $allVouchers = $this->getvouchers();
        $vouchers = $allVouchers->where('status', $status);
        $this->selectedForBulk = $vouchers->pluck('uuid')->toArray();
    }

    public function clearBulkSelection()
    {
        $this->selectedForBulk = [];
    }

    public function openBulkApprovalModal($status)
    {
        if (empty($this->selectedForBulk)) {
            $this->error('Please select at least one voucher to approve');

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
            $voucher = $this->getVoucherByUuid($uuid);
            if (! $voucher) {
                $failCount++;
                $errors[] = "Voucher {$uuid} not found";

                continue;
            }

            // Use the dynamic approve method
            $response = $this->paymentvoucherService->approve($voucher->id, [
                'comment' => $this->bulkComment ?? 'Bulk approved',
                'authorization_code' => $this->bulkApprovalCode,
            ]);

            if ($response && $response['status'] == 'success') {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "{$voucher->voucher_number}: ".($response['message'] ?? 'Failed');
            }
        }

        if ($successCount > 0) {
            $this->success("{$successCount} voucher(s) approved successfully");
        }

        if ($failCount > 0) {
            $this->error("{$failCount} voucher(s) failed: ".implode(', ', array_slice($errors, 0, 3)));
        }

        $this->reset(['selectedForBulk', 'bulkComment', 'bulkApprovalCode', 'bulkStepOrder', 'bulkStepName', 'bulkStatus']);
        $this->bulkapprovalmodal = false;
        // Force component refresh to show updated voucher list
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $workflow = $this->getworkflow();
        $vouchers = $this->getvouchers();

        return view('livewire.admin.workflows.approvals.payment-voucherlist', [
            'workflow' => $workflow,
            'vouchers' => $vouchers,
        ]);
    }
}
