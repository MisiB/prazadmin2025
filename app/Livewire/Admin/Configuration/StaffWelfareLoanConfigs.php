<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\services\istaffwelfareloanService;
use Livewire\Component;
use Mary\Traits\Toast;

class StaffWelfareLoanConfigs extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $modal = false;

    public $id;

    // Form fields
    public $interest_rate = 0;

    public $max_repayment_months = 24;

    public $max_loan_amount;

    public $min_loan_amount = 0;

    public $notes;

    protected $staffwelfareloanService;

    public function boot(istaffwelfareloanService $staffwelfareloanService)
    {
        $this->staffwelfareloanService = $staffwelfareloanService;
    }

    public function mount()
    {
        // Check if user has permission to view (at minimum)
        if (! auth()->user()->can('swl.config.manage') && ! auth()->user()->can('swl.view.hr.queue')) {
            abort(403, 'You do not have permission to access loan configuration.');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Staff Welfare Loan Configuration'],
        ];

        // Load active config if exists
        $this->loadActiveConfig();
    }

    public function loadActiveConfig(): void
    {
        $config = $this->staffwelfareloanService->getActiveConfig();
        if ($config) {
            $this->id = $config->id;
            $this->interest_rate = $config->interest_rate;
            $this->max_repayment_months = $config->max_repayment_months;
            $this->max_loan_amount = $config->max_loan_amount;
            $this->min_loan_amount = $config->min_loan_amount;
            $this->notes = $config->notes;
        }
    }

    public function save()
    {
        // Check permission before saving
        if (! auth()->user()->can('swl.config.manage')) {
            $this->error('You do not have permission to manage loan configuration.');

            return;
        }

        $this->validate([
            'interest_rate' => 'required|numeric|min:0|max:100',
            'max_repayment_months' => 'required|integer|min:1|max:120',
            'max_loan_amount' => 'nullable|numeric|min:0',
            'min_loan_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = [
            'interest_rate' => $this->interest_rate,
            'max_repayment_months' => $this->max_repayment_months,
            'max_loan_amount' => $this->max_loan_amount,
            'min_loan_amount' => $this->min_loan_amount,
            'notes' => $this->notes,
        ];

        if ($this->id) {
            $response = $this->staffwelfareloanService->updateConfig($this->id, $data);
        } else {
            $response = $this->staffwelfareloanService->createConfig($data);
        }

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->loadActiveConfig();
        } else {
            $this->error($response['message']);
        }
    }

    /**
     * Preview calculation for given parameters
     */
    public function previewCalculation($principal, $months): array
    {
        if (! $principal || ! $months) {
            return [];
        }

        return $this->staffwelfareloanService->calculateLoanRepayment(
            (float) $principal,
            (float) $this->interest_rate,
            (int) $months
        );
    }

    public function render()
    {
        return view('livewire.admin.configuration.staff-welfare-loan-configs');
    }
}
