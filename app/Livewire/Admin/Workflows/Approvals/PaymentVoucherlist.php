<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
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

    protected $paymentvoucherService;

    protected $authrepo;

    protected $workflowRepository;

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

    public function boot(ipaymentvoucherService $paymentvoucherService, iauthInterface $authrepo, iworkflowInterface $workflowRepository)
    {
        $this->paymentvoucherService = $paymentvoucherService;
        $this->authrepo = $authrepo;
        $this->workflowRepository = $workflowRepository;
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
            } else {
                $this->error($response['message'] ?? 'An error occurred');
            }
        } else {
            $this->error($checkcode['message']);
        }
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
