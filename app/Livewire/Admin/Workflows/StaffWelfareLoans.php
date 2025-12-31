<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\services\istaffwelfareloanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class StaffWelfareLoans extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $search;

    public $year;

    public $modal;

    public $id;

    // Applicant Section Fields
    public $employee_number;

    public $job_title;

    public $date_joined;

    public $loan_amount_requested;

    public $loan_purpose;

    public $repayment_period_months;

    // Config limits (for display in view)
    public $min_loan_amount = 0;

    public $max_loan_amount = null;

    public $max_repayment_months = 24;

    protected $staffwelfareloanService;

    public function boot(istaffwelfareloanService $staffwelfareloanService)
    {
        $this->staffwelfareloanService = $staffwelfareloanService;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Staff Welfare Loans'],
        ];
        $this->year = date('Y');
        $this->search = '';
        $this->loadConfigLimits();
    }

    protected function loadConfigLimits(): void
    {
        $config = $this->staffwelfareloanService->getActiveConfig();
        if ($config) {
            $this->min_loan_amount = $config->min_loan_amount ?? 0;
            $this->max_loan_amount = $config->max_loan_amount;
            $this->max_repayment_months = $config->max_repayment_months ?? 24;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getloans()
    {
        return $this->staffwelfareloanService->getloansbyapplicant(Auth::user()->id, $this->year, $this->search);
    }

    public function edit($id)
    {
        $loan = $this->staffwelfareloanService->getloan($id);
        $this->id = $id;
        $this->employee_number = $loan->employee_number;
        $this->job_title = $loan->job_title;
        $this->date_joined = $loan->date_joined?->format('Y-m-d');
        $this->loan_amount_requested = $loan->loan_amount_requested;
        $this->loan_purpose = $loan->loan_purpose;
        $this->repayment_period_months = $loan->repayment_period_months;
        $this->modal = true;
    }

    public function save()
    {
        // Build dynamic validation rules based on config
        $loanAmountRules = ['required', 'numeric', 'min:'.($this->min_loan_amount > 0 ? $this->min_loan_amount : 0)];
        if ($this->max_loan_amount && $this->max_loan_amount > 0) {
            $loanAmountRules[] = 'max:'.$this->max_loan_amount;
        }

        $repaymentRules = ['required', 'integer', 'min:1'];
        if ($this->max_repayment_months && $this->max_repayment_months > 0) {
            $repaymentRules[] = 'max:'.$this->max_repayment_months;
        }

        $this->validate([
            'employee_number' => 'required|string',
            'job_title' => 'required|string',
            'date_joined' => 'required|date',
            'loan_amount_requested' => $loanAmountRules,
            'loan_purpose' => 'required|string',
            'repayment_period_months' => $repaymentRules,
        ], [
            'loan_amount_requested.min' => 'Loan amount must be at least $'.number_format($this->min_loan_amount, 2),
            'loan_amount_requested.max' => 'Loan amount cannot exceed $'.number_format($this->max_loan_amount ?? 0, 2),
            'repayment_period_months.max' => 'Repayment period cannot exceed '.$this->max_repayment_months.' months',
        ]);

        if ($this->id != null) {
            $this->update();
        } else {
            $this->create();
        }

        $this->reset(['employee_number', 'job_title', 'date_joined', 'loan_amount_requested', 'loan_purpose', 'repayment_period_months', 'id']);
    }

    public function create()
    {
        $response = $this->staffwelfareloanService->createloan([
            'employee_number' => $this->employee_number,
            'job_title' => $this->job_title,
            'date_joined' => $this->date_joined,
            'loan_amount_requested' => $this->loan_amount_requested,
            'loan_purpose' => $this->loan_purpose,
            'repayment_period_months' => $this->repayment_period_months,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function update()
    {
        $response = $this->staffwelfareloanService->updateloan($this->id, [
            'employee_number' => $this->employee_number,
            'job_title' => $this->job_title,
            'date_joined' => $this->date_joined,
            'loan_amount_requested' => $this->loan_amount_requested,
            'loan_purpose' => $this->loan_purpose,
            'repayment_period_months' => $this->repayment_period_months,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->staffwelfareloanService->deleteloan($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function submit($id)
    {
        $response = $this->staffwelfareloanService->submitloan($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'loan_number', 'label' => 'Loan Number'],
            ['key' => 'full_name', 'label' => 'Applicant'],
            ['key' => 'loan_amount_requested', 'label' => 'Amount Requested'],
            ['key' => 'loan_purpose', 'label' => 'Purpose'],
            ['key' => 'repayment_period_months', 'label' => 'Repayment Period'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.staff-welfare-loans', [
            'breadcrumbs' => $this->breadcrumbs,
            'loans' => $this->getloans(),
            'headers' => $this->headers(),
        ]);
    }
}
