<?php

namespace App\Livewire\Admin\Workflows\Components;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\services\istaffwelfareloanService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class StaffWelfareLoandata extends Component
{
    use Toast, WithFileUploads;

    public $uuid;

    public $modal;

    public $decision;

    public $comment;

    public $approvalcode;

    public $selectedTab = 'details-tab';

    public $decisionmodal;

    public $hrdatamodal;

    public $paymentmodal;

    public $acknowledgementmodal;

    public $loan_id;

    // HR Data Fields
    public $employment_status;

    public $date_of_engagement;

    public $basic_salary;

    public $monthly_deduction_amount;

    public $existing_loan_balance;

    public $monthly_repayment;

    public $hr_comments;

    // Payment Fields
    public $amount_paid;

    public $payment_method;

    public $payment_reference;

    public $payment_date;

    public $proof_of_payment;

    public $payment_notes;

    // Acknowledgement Fields
    public $acknowledgement_statement;

    protected $staffwelfareloanService;

    protected $authrepo;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
    }

    public function boot(istaffwelfareloanService $staffwelfareloanService, iauthInterface $authrepo)
    {
        $this->staffwelfareloanService = $staffwelfareloanService;
        $this->authrepo = $authrepo;
    }

    public function getloan()
    {
        $loan = $this->staffwelfareloanService->getloanbyuuid($this->uuid);
        $this->loan_id = $loan->id;

        return $loan;
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
            if ($this->decision == 'APPROVED') {
                $response = $this->staffwelfareloanService->approve($this->loan_id, [
                    'comment' => $this->comment ?? '',
                    'authorization_code' => $this->approvalcode,
                ]);
            } else {
                $response = $this->staffwelfareloanService->reject($this->loan_id, [
                    'comment' => $this->comment,
                    'authorization_code' => $this->approvalcode,
                ]);
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

    public function savehrdata()
    {
        $this->validate([
            'employment_status' => 'required|string',
            'date_of_engagement' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'hr_comments' => 'nullable|string',
        ]);

        // Only pass required fields - deductions, balances, and last_payment_date are auto-calculated
        $response = $this->staffwelfareloanService->capturehrdata($this->loan_id, [
            'employment_status' => $this->employment_status,
            'date_of_engagement' => $this->date_of_engagement,
            'basic_salary' => $this->basic_salary,
            'hr_comments' => $this->hr_comments,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['employment_status', 'date_of_engagement', 'basic_salary', 'monthly_deduction_amount', 'existing_loan_balance', 'monthly_repayment', 'hr_comments']);
            $this->hrdatamodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function executepayment()
    {
        $this->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
            'payment_date' => 'required|date',
            'proof_of_payment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'payment_notes' => 'nullable|string',
        ]);

        // Store the file
        $proofPath = $this->proof_of_payment->store('staff-welfare-loan-payments', 'public');

        $response = $this->staffwelfareloanService->executepayment($this->loan_id, [
            'amount_paid' => $this->amount_paid,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'proof_of_payment_path' => $proofPath,
            'notes' => $this->payment_notes,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['amount_paid', 'payment_method', 'payment_reference', 'payment_date', 'proof_of_payment', 'payment_notes']);
            $this->paymentmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function acknowledgedebt()
    {
        $this->validate([
            'acknowledgement_statement' => 'required|string|min:10',
        ]);

        $response = $this->staffwelfareloanService->acknowledgedebt($this->loan_id, [
            'acknowledgement_statement' => $this->acknowledgement_statement,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['acknowledgement_statement']);
            $this->acknowledgementmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.workflows.components.staff-welfare-loandata', [
            'loan' => $this->getloan(),
        ]);
    }
}
