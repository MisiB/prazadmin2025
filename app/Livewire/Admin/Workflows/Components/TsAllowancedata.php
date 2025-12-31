<?php

namespace App\Livewire\Admin\Workflows\Components;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\services\itsallowanceService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class TsAllowancedata extends Component
{
    use Toast, WithFileUploads;

    public $uuid;

    public $modal;

    public $decision;

    public $comment;

    public $approvalcode;

    public $selectedTab = 'details-tab';

    public $decisionmodal;

    public $financeverificationmodal;

    public $paymentmodal;

    public $allowance_id;

    // Finance Verification Fields
    public $verified_total_amount;

    public $exchange_rate_id;

    public $exchange_rate_applied;

    public $finance_comment;

    // Payment Fields
    public $currency_id;

    public $amount_paid_usd;

    public $amount_paid_original;

    public $payment_method;

    public $payment_reference;

    public $payment_date;

    public $proof_of_payment;

    public $payment_notes;

    protected $tsallowanceService;

    protected $authrepo;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
    }

    public function boot(itsallowanceService $tsallowanceService, iauthInterface $authrepo)
    {
        $this->tsallowanceService = $tsallowanceService;
        $this->authrepo = $authrepo;
    }

    public function getallowance()
    {
        $allowance = $this->tsallowanceService->getallowancebyuuid($this->uuid);
        $this->allowance_id = $allowance->id;

        return $allowance;
    }

    public function savedecision()
    {
        $this->validate([
            'decision' => 'required',
            'comment' => 'required_if:decision,REJECT',
            'approvalcode' => 'required',
        ]);

        $checkcode = $this->authrepo->checkapprovalcode($this->approvalcode);
        if ($checkcode['status'] == 'success') {
            $allowance = $this->getallowance();

            // Determine if this is a recommendation or approval based on status
            if ($allowance->status == 'SUBMITTED') {
                // HOD Recommendation
                if ($this->decision == 'APPROVED') {
                    $response = $this->tsallowanceService->recommend($this->allowance_id, [
                        'comment' => $this->comment ?? '',
                        'authorization_code' => $this->approvalcode,
                        'hod_designation' => auth()->user()->job_title ?? 'HOD',
                    ]);
                } else {
                    $response = $this->tsallowanceService->rejectrecommendation($this->allowance_id, [
                        'comment' => $this->comment,
                        'authorization_code' => $this->approvalcode,
                    ]);
                }
            } else {
                // CEO/Other Approval
                if ($this->decision == 'APPROVED') {
                    $response = $this->tsallowanceService->approve($this->allowance_id, [
                        'comment' => $this->comment ?? '',
                        'authorization_code' => $this->approvalcode,
                    ]);
                } else {
                    $response = $this->tsallowanceService->reject($this->allowance_id, [
                        'comment' => $this->comment,
                        'authorization_code' => $this->approvalcode,
                    ]);
                }
            }

            if ($response['status'] == 'success') {
                $this->success($response['message']);
                $this->reset(['decision', 'comment', 'approvalcode']);
                $this->decisionmodal = false;
            } else {
                $this->error($response['message']);
            }
        } else {
            $this->error($checkcode['message']);
        }
    }

    public function savefinanceverification()
    {
        $this->validate([
            'verified_total_amount' => 'required|numeric|min:0',
            'exchange_rate_id' => 'nullable|exists:exchangerates,id',
            'exchange_rate_applied' => 'nullable|numeric|min:0',
            'finance_comment' => 'nullable|string',
        ]);

        $response = $this->tsallowanceService->verifyfinance($this->allowance_id, [
            'verified_total_amount' => $this->verified_total_amount,
            'exchange_rate_id' => $this->exchange_rate_id,
            'exchange_rate_applied' => $this->exchange_rate_applied,
            'finance_comment' => $this->finance_comment,
            'verified_allowance_rates' => [], // Could be populated with breakdown
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['verified_total_amount', 'exchange_rate_id', 'exchange_rate_applied', 'finance_comment']);
            $this->financeverificationmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function executepayment()
    {
        $this->validate([
            'currency_id' => 'nullable|exists:currencies,id',
            'amount_paid_usd' => 'required|numeric|min:0',
            'amount_paid_original' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
            'payment_date' => 'required|date',
            'proof_of_payment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'payment_notes' => 'nullable|string',
        ]);

        // Store the file if provided
        $proofPath = null;
        if ($this->proof_of_payment) {
            $proofPath = $this->proof_of_payment->store('ts-allowance-payments', 'public');
        }

        $response = $this->tsallowanceService->processpayment($this->allowance_id, [
            'currency_id' => $this->currency_id,
            'amount_paid_usd' => $this->amount_paid_usd,
            'amount_paid_original' => $this->amount_paid_original ?? $this->amount_paid_usd,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'proof_of_payment_path' => $proofPath,
            'payment_notes' => $this->payment_notes,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['currency_id', 'amount_paid_usd', 'amount_paid_original', 'payment_method', 'payment_reference', 'payment_date', 'proof_of_payment', 'payment_notes']);
            $this->paymentmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.workflows.components.ts-allowancedata', [
            'allowance' => $this->getallowance(),
        ]);
    }
}
