<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\services\istaffwelfareloanService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class StaffWelfareLoanPayments extends Component
{
    use Toast, WithFileUploads;

    public $breadcrumbs = [];

    public $year;

    protected $staffwelfareloanService;

    protected $authrepo;

    // Expanded loans tracking
    public $expandedLoans = [];

    // Selected loan for payment
    public $selectedLoanUuid = null;

    public $selectedLoanId = null;

    // Payment modal
    public $paymentmodal = false;

    public $currency_id;

    public $exchangerate_id;

    public $amount_paid;

    public $amount_paid_usd;

    public $exchange_rate_used;

    public $payment_method;

    public $payment_reference;

    public $payment_date;

    public $proof_of_payment;

    public $payment_notes;

    public function mount()
    {
        $this->year = date('Y');
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Staff Welfare Loan Payments'],
        ];
    }

    public function boot(istaffwelfareloanService $staffwelfareloanService, iauthInterface $authrepo)
    {
        $this->staffwelfareloanService = $staffwelfareloanService;
        $this->authrepo = $authrepo;
    }

    public function getApprovedLoans()
    {
        return $this->staffwelfareloanService->getloansbystatus($this->year, 'APPROVED');
    }

    public function getPaymentProcessedToday()
    {
        $loans = $this->staffwelfareloanService->getallloans($this->year);

        return $loans->filter(function ($loan) {
            return in_array($loan->status, ['PAYMENT_PROCESSED', 'AWAITING_ACKNOWLEDGEMENT', 'COMPLETED'])
                && $loan->payment_capture_date?->isToday();
        })->count();
    }

    public function getPaymentProcessedThisMonth()
    {
        $loans = $this->staffwelfareloanService->getallloans($this->year);

        return $loans->filter(function ($loan) {
            return in_array($loan->status, ['PAYMENT_PROCESSED', 'AWAITING_ACKNOWLEDGEMENT', 'COMPLETED'])
                && $loan->payment_capture_date?->isCurrentMonth();
        })->count();
    }

    public function toggleLoan($uuid)
    {
        if (isset($this->expandedLoans[$uuid])) {
            unset($this->expandedLoans[$uuid]);
        } else {
            $this->expandedLoans[$uuid] = true;
        }
    }

    public function isLoanExpanded($uuid)
    {
        return isset($this->expandedLoans[$uuid]);
    }

    public function getLoanByUuid($uuid)
    {
        return $this->staffwelfareloanService->getloanbyuuid($uuid);
    }

    public function getCurrenciesProperty()
    {
        return \App\Models\Currency::where('status', 'active')->get();
    }

    public function getSelectedCurrencyProperty()
    {
        if (! $this->currency_id) {
            return null;
        }

        return \App\Models\Currency::find($this->currency_id);
    }

    public function getAvailableExchangeRatesProperty()
    {
        if (! $this->currency_id) {
            return collect();
        }

        return \App\Models\Exchangerate::where('secondary_currency_id', $this->currency_id)
            ->with(['primarycurrency', 'secondarycurrency', 'user'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function updatedCurrencyId()
    {
        $this->exchangerate_id = null;
        $this->exchange_rate_used = null;
        $this->amount_paid = null;
        $this->amount_paid_usd = null;

        // If USD is selected, set amount_paid to the loan amount requested
        $currency = $this->selectedCurrency;
        if ($currency && ($currency->name === 'USD' || $currency->name === 'US Dollar')) {
            $loan = $this->getLoanByUuid($this->selectedLoanUuid);
            $this->amount_paid = $loan->loan_amount_requested;
            $this->amount_paid_usd = $loan->loan_amount_requested;
        }
    }

    public function updatedExchangerateId()
    {
        if ($this->exchangerate_id) {
            $rate = \App\Models\Exchangerate::find($this->exchangerate_id);
            $this->exchange_rate_used = $rate->value;

            // Auto-calculate ZIG amount from USD loan amount
            $loan = $this->getLoanByUuid($this->selectedLoanUuid);
            if ($loan && $this->exchange_rate_used) {
                $this->amount_paid_usd = $loan->loan_amount_requested;
                $this->amount_paid = $loan->loan_amount_requested * $this->exchange_rate_used;
            }
        } else {
            $this->exchange_rate_used = null;
            $this->amount_paid = null;
            $this->amount_paid_usd = null;
        }
    }

    public function updatedAmountPaid()
    {
        $this->calculateUsdAmount();
    }

    private function calculateUsdAmount()
    {
        if (! $this->amount_paid || ! $this->currency_id) {
            $this->amount_paid_usd = null;

            return;
        }

        $currency = $this->selectedCurrency;

        if ($currency->name === 'USD' || $currency->name === 'US Dollar') {
            $this->amount_paid_usd = $this->amount_paid;
        } elseif ($this->exchange_rate_used) {
            $this->amount_paid_usd = $this->amount_paid / $this->exchange_rate_used;
        }
    }

    public function openPaymentModal($uuid)
    {
        $this->selectedLoanUuid = $uuid;
        $loan = $this->getLoanByUuid($uuid);
        $this->selectedLoanId = $loan->id;
        $this->amount_paid = $loan->loan_amount_requested;
        $this->paymentmodal = true;
    }

    public function executepayment()
    {
        $rules = [
            'currency_id' => 'required|exists:currencies,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
            'payment_date' => 'required|date',
            'proof_of_payment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'payment_notes' => 'nullable|string',
        ];

        $currency = \App\Models\Currency::find($this->currency_id);
        if ($currency->name !== 'USD' && $currency->name !== 'US Dollar') {
            $rules['exchangerate_id'] = 'required|exists:exchangerates,id';
        }

        $this->validate($rules);

        $proofPath = $this->proof_of_payment->store('staff-welfare-loan-payments', 'public');

        $response = $this->staffwelfareloanService->executepayment($this->selectedLoanId, [
            'currency_id' => $this->currency_id,
            'exchangerate_id' => $this->exchangerate_id,
            'amount_paid' => $this->amount_paid,
            'amount_paid_original' => $this->amount_paid,
            'amount_paid_usd' => $this->amount_paid_usd,
            'exchange_rate_used' => $this->exchange_rate_used,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'proof_of_payment_path' => $proofPath,
            'notes' => $this->payment_notes,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['currency_id', 'exchangerate_id', 'amount_paid', 'amount_paid_usd', 'exchange_rate_used', 'payment_method', 'payment_reference', 'payment_date', 'proof_of_payment', 'payment_notes', 'selectedLoanUuid', 'selectedLoanId']);
            $this->paymentmodal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'loan_number', 'label' => 'Loan Number'],
            ['key' => 'full_name', 'label' => 'Applicant'],
            ['key' => 'department.name', 'label' => 'Department'],
            ['key' => 'loan_amount_requested', 'label' => 'Amount'],
            ['key' => 'submission_date', 'label' => 'Submitted'],
            ['key' => 'days_waiting', 'label' => 'Days Waiting'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.staff-welfare-loan-payments', [
            'approvedLoans' => $this->getApprovedLoans(),
            'paymentsToday' => $this->getPaymentProcessedToday(),
            'paymentsThisMonth' => $this->getPaymentProcessedThisMonth(),
            'headers' => $this->headers(),
        ]);
    }
}
