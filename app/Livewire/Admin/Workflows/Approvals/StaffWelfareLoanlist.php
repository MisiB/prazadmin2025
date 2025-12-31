<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\iworkflowInterface;
use App\Interfaces\services\istaffwelfareloanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class StaffWelfareLoanlist extends Component
{
    use Toast, WithFileUploads;

    public $breadcrumbs = [];

    public $selectedTabs = [];

    public $year;

    public $departmentId = 0;

    public $user;

    protected $staffwelfareloanService;

    protected $workflowRepository;

    protected $authrepo;

    // Expanded stages tracking
    public $expandedStages = [];

    // Selected loan for actions
    public $selectedLoanUuid = null;

    public $selectedLoanId = null;

    public $expandedLoans = [];

    // Decision modal
    public $decisionmodal = false;

    public $decision;

    public $comment;

    public $approvalcode;

    // HR Data modal
    public $hrdatamodal = false;

    public $employment_status;

    public $date_of_engagement;

    public $basic_salary;

    public $monthly_deduction_amount;

    public $existing_loan_balance;

    public $monthly_repayment;

    public $hr_comments;

    public $selectedLoanRepaymentMonths = 0;

    // Payment modal
    public $paymentmodal = false;

    public $amount_paid;

    public $payment_method;

    public $payment_reference;

    public $payment_date;

    public $proof_of_payment;

    public $payment_notes;

    // Acknowledgement modal
    public $acknowledgementmodal = false;

    public $acknowledgement_statement;

    public function boot(istaffwelfareloanService $staffwelfareloanService, iworkflowInterface $workflowRepository, iauthInterface $authrepo)
    {
        $this->staffwelfareloanService = $staffwelfareloanService;
        $this->workflowRepository = $workflowRepository;
        $this->authrepo = $authrepo;
    }

    public function mount()
    {
        $this->year = date('Y');
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Staff Welfare Loan Approvals'],
        ];
        $this->user = Auth::user();

        // Get user's department ID for HOD filtering
        try {
            $this->departmentId = $this->staffwelfareloanService->getuserdepartmentid($this->user->email);
        } catch (\Exception $e) {
            $this->departmentId = 0;
        }
    }

    public function getloanlist()
    {
        // If user has permission to view all, return all loans
        if ($this->user->can('swl.view.all')) {
            return $this->staffwelfareloanService->getallloans($this->year);
        }

        // User doesn't have view.all - need to filter by workflow step
        // Get workflow to check which steps user can access
        $workflow = $this->getworkflowbystatus();
        if (! $workflow) {
            return collect();
        }

        $filteredLoans = collect();

        foreach ($workflow->workflowparameters as $workflowParameter) {
            // Check if user has permission for this workflow step
            if (! $this->user->can($workflowParameter->permission->name)) {
                continue;
            }

            // If this is the HOD step (first step, order 1), filter by department
            if ($workflowParameter->order == 1) {
                // HOD step - get only department's loans for this status
                $deptLoans = $this->staffwelfareloanService->getdeptloans($this->year, $this->departmentId);
                $statusLoans = $deptLoans->where('status', $workflowParameter->status);
                $filteredLoans = $filteredLoans->merge($statusLoans);
            } else {
                // Any step after HOD (order > 1) - user has permission, so show all for this status
                $allLoans = $this->staffwelfareloanService->getallloans($this->year);
                $statusLoans = $allLoans->where('status', $workflowParameter->status);
                $filteredLoans = $filteredLoans->merge($statusLoans);
            }
        }

        return $filteredLoans;
    }

    public function getworkflowbystatus()
    {
        $name = config('workflow.staff_welfare_loans');

        return $this->workflowRepository->getworkflowbystatus($name);
    }

    public function toggleStage($status)
    {
        if (isset($this->expandedStages[$status])) {
            unset($this->expandedStages[$status]);
            // Also collapse all loans in this stage
            foreach ($this->expandedLoans as $uuid => $value) {
                $loan = $this->getloanlist()->firstWhere('uuid', $uuid);
                if ($loan && $loan->status === $status) {
                    unset($this->expandedLoans[$uuid]);
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

    public function toggleLoan($uuid)
    {
        if (isset($this->expandedLoans[$uuid])) {
            unset($this->expandedLoans[$uuid]);
            if ($this->selectedLoanUuid === $uuid) {
                $this->selectedLoanUuid = null;
                $this->selectedLoanId = null;
            }
        } else {
            $this->expandedLoans[$uuid] = true;
            $this->selectLoan($uuid);
        }
    }

    public function isLoanExpanded($uuid)
    {
        return isset($this->expandedLoans[$uuid]);
    }

    public function selectLoan($uuid)
    {
        $this->selectedLoanUuid = $uuid;
        $loan = $this->staffwelfareloanService->getloanbyuuid($uuid);
        $this->selectedLoanId = $loan->id;
    }

    public function getLoanByUuid($uuid)
    {
        return $this->staffwelfareloanService->getloanbyuuid($uuid);
    }

    public function openDecisionModal($uuid)
    {
        $this->selectLoan($uuid);
        $this->decisionmodal = true;
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
                $response = $this->staffwelfareloanService->approve($this->selectedLoanId, [
                    'comment' => $this->comment ?? '',
                    'authorization_code' => $this->approvalcode,
                ]);
            } else {
                $response = $this->staffwelfareloanService->reject($this->selectedLoanId, [
                    'comment' => $this->comment,
                    'authorization_code' => $this->approvalcode,
                ]);
            }

            if ($response['status'] == 'success') {
                $this->success($response['message']);
                $this->reset(['decision', 'comment', 'approvalcode', 'selectedLoanUuid', 'selectedLoanId']);
                $this->decisionmodal = false;
            } else {
                $this->error($response['message']);
            }
        } else {
            $this->error($checkcode['message']);
        }
    }

    public function openHrDataModal($uuid)
    {
        $this->selectLoan($uuid);
        $loan = $this->getLoanByUuid($uuid);
        if ($loan) {
            $this->employment_status = $loan->employment_status;
            $this->date_of_engagement = $loan->date_of_engagement?->format('Y-m-d');
            $this->basic_salary = $loan->basic_salary;
            $this->monthly_deduction_amount = $loan->monthly_deduction_amount;
            $this->existing_loan_balance = $loan->existing_loan_balance;
            $this->monthly_repayment = $loan->monthly_repayment;
            $this->hr_comments = $loan->hr_comments;
            $this->selectedLoanRepaymentMonths = $loan->repayment_period_months ?? 0;
        }
        $this->hrdatamodal = true;
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
        $response = $this->staffwelfareloanService->capturehrdata($this->selectedLoanId, [
            'employment_status' => $this->employment_status,
            'date_of_engagement' => $this->date_of_engagement,
            'basic_salary' => $this->basic_salary,
            'hr_comments' => $this->hr_comments,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['employment_status', 'date_of_engagement', 'basic_salary', 'monthly_deduction_amount', 'existing_loan_balance', 'monthly_repayment', 'hr_comments', 'selectedLoanUuid', 'selectedLoanId']);
            $this->hrdatamodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function openPaymentModal($uuid)
    {
        $this->selectLoan($uuid);
        $this->paymentmodal = true;
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

        $proofPath = $this->proof_of_payment->store('staff-welfare-loan-payments', 'public');

        $response = $this->staffwelfareloanService->executepayment($this->selectedLoanId, [
            'amount_paid' => $this->amount_paid,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'proof_of_payment_path' => $proofPath,
            'notes' => $this->payment_notes,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['amount_paid', 'payment_method', 'payment_reference', 'payment_date', 'proof_of_payment', 'payment_notes', 'selectedLoanUuid', 'selectedLoanId']);
            $this->paymentmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function openAcknowledgementModal($uuid)
    {
        $this->selectLoan($uuid);
        $this->acknowledgementmodal = true;
    }

    public function acknowledgedebt()
    {
        $this->validate([
            'acknowledgement_statement' => 'required|string|min:10',
        ]);

        $response = $this->staffwelfareloanService->acknowledgedebt($this->selectedLoanId, [
            'acknowledgement_statement' => $this->acknowledgement_statement,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['acknowledgement_statement', 'selectedLoanUuid', 'selectedLoanId']);
            $this->acknowledgementmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.staff-welfare-loanlist', [
            'loans' => $this->getloanlist(),
            'workflow' => $this->getworkflowbystatus(),
        ]);
    }
}
