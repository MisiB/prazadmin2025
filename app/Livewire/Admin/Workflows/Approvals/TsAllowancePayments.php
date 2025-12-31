<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\services\itsallowanceService;
use App\Models\Currency;
use App\Models\Exchangerate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class TsAllowancePayments extends Component
{
    use Toast, WithFileUploads;

    public $breadcrumbs = [];

    public $year;

    protected $tsallowanceService;

    protected $authrepo;

    // Expanded allowances tracking
    public $expandedAllowances = [];

    // Selected allowance for actions
    public $selectedAllowanceUuid = null;

    public $selectedAllowanceId = null;

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

    // Split Payment
    public $isSplitPayment = false;

    public $usd_amount = 0;

    public $usd_percentage = 0;

    public $local_currency_id;

    public $local_exchangerate_id;

    public $local_amount = 0;

    public $local_percentage = 0;

    public $local_exchange_rate_used;

    public function mount()
    {
        $this->year = date('Y');
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'T&S Allowance Payments'],
        ];
    }

    public function boot(itsallowanceService $tsallowanceService, iauthInterface $authrepo)
    {
        $this->tsallowanceService = $tsallowanceService;
        $this->authrepo = $authrepo;
    }

    public function getAwaitingPaymentAllowances()
    {
        // Get allowances with APPROVED status
        $allowances = $this->tsallowanceService->getallowancesbystatus($this->year, 'APPROVED');

        // Filter to only show allowances where ALL workflow stages have been approved
        return $allowances->filter(function ($allowance) {
            // Get the workflow parameters count (number of approval stages)
            $workflowStepsCount = $allowance->workflow?->workflowparameters?->count() ?? 0;

            // Get the number of APPROVED approvals for this allowance
            $approvedCount = $allowance->approvals?->where('status', 'APPROVED')->count() ?? 0;

            // Only show if all workflow stages have been approved
            return $workflowStepsCount > 0 && $approvedCount >= $workflowStepsCount;
        });
    }

    public function getPaymentProcessedToday()
    {
        $allowances = $this->tsallowanceService->getallallowances($this->year);

        return $allowances->filter(function ($allowance) {
            return $allowance->status === 'PAYMENT_PROCESSED'
                && $allowance->payment_capture_date?->isToday();
        })->count();
    }

    public function getPaymentProcessedThisMonth()
    {
        $allowances = $this->tsallowanceService->getallallowances($this->year);

        return $allowances->filter(function ($allowance) {
            return $allowance->status === 'PAYMENT_PROCESSED'
                && $allowance->payment_capture_date?->isCurrentMonth();
        })->count();
    }

    public function getTotalPaidThisMonth()
    {
        $allowances = $this->tsallowanceService->getallallowances($this->year);

        return $allowances->filter(function ($allowance) {
            return $allowance->status === 'PAYMENT_PROCESSED'
                && $allowance->payment_capture_date?->isCurrentMonth();
        })->sum('amount_paid_usd');
    }

    public function getTotalPaidByCurrencyThisMonth()
    {
        $allowances = $this->tsallowanceService->getallallowances($this->year);

        $paidAllowances = $allowances->filter(function ($allowance) {
            return $allowance->status === 'PAYMENT_PROCESSED'
                && $allowance->payment_capture_date?->isCurrentMonth();
        });

        // Group by currency and sum amounts
        $byCurrency = [];

        foreach ($paidAllowances as $allowance) {
            $currencyName = $allowance->currency?->name ?? 'USD';
            $currencyId = $allowance->currency_id ?? 0;

            if (! isset($byCurrency[$currencyId])) {
                $byCurrency[$currencyId] = [
                    'currency_name' => $currencyName,
                    'total_original' => 0,
                    'total_usd' => 0,
                    'count' => 0,
                ];
            }

            $byCurrency[$currencyId]['total_original'] += $allowance->amount_paid_original ?? $allowance->amount_paid_usd;
            $byCurrency[$currencyId]['total_usd'] += $allowance->amount_paid_usd ?? 0;
            $byCurrency[$currencyId]['count']++;
        }

        return collect($byCurrency)->sortByDesc('total_usd');
    }

    public function toggleAllowance($uuid)
    {
        if (isset($this->expandedAllowances[$uuid])) {
            unset($this->expandedAllowances[$uuid]);
        } else {
            $this->expandedAllowances[$uuid] = true;
        }
    }

    public function isAllowanceExpanded($uuid)
    {
        return isset($this->expandedAllowances[$uuid]);
    }

    public function getAllowanceByUuid($uuid)
    {
        return $this->tsallowanceService->getallowancebyuuid($uuid);
    }

    public function getCurrenciesProperty()
    {
        return Currency::where('status', 'active')->get();
    }

    public function getSelectedCurrencyProperty()
    {
        if (! $this->currency_id) {
            return null;
        }

        return Currency::find($this->currency_id);
    }

    public function getAvailableExchangeRatesProperty()
    {
        if (! $this->currency_id) {
            return collect();
        }

        return Exchangerate::where('secondary_currency_id', $this->currency_id)
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

        // If USD is selected, set amount_paid to the allowance balance due
        $currency = $this->selectedCurrency;
        if ($currency && ($currency->name === 'USD' || $currency->name === 'US Dollar')) {
            $allowance = $this->getAllowanceByUuid($this->selectedAllowanceUuid);
            $this->amount_paid = $allowance->balance_due;
            $this->amount_paid_usd = $allowance->balance_due;
        }
    }

    public function updatedExchangerateId()
    {
        if ($this->exchangerate_id) {
            $rate = Exchangerate::find($this->exchangerate_id);
            $this->exchange_rate_used = $rate->value;

            // Auto-calculate amount from USD balance due
            $allowance = $this->getAllowanceByUuid($this->selectedAllowanceUuid);
            if ($allowance && $this->exchange_rate_used) {
                $this->amount_paid_usd = $allowance->balance_due;
                $this->amount_paid = $allowance->balance_due * $this->exchange_rate_used;
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

    // Split Payment Methods
    public function updatedIsSplitPayment()
    {
        if ($this->isSplitPayment) {
            // Initialize split payment with 50/50 by default
            $allowance = $this->getAllowanceByUuid($this->selectedAllowanceUuid);
            if ($allowance) {
                $this->usd_percentage = 50;
                $this->local_percentage = 50;
                $this->usd_amount = round($allowance->balance_due * 0.5, 2);
            }
        } else {
            $this->resetSplitPaymentFields();
        }
    }

    public function updatedUsdPercentage()
    {
        $this->local_percentage = 100 - $this->usd_percentage;
        $this->calculateSplitAmounts();
    }

    public function updatedLocalPercentage()
    {
        $this->usd_percentage = 100 - $this->local_percentage;
        $this->calculateSplitAmounts();
    }

    public function updatedUsdAmount()
    {
        $allowance = $this->getAllowanceByUuid($this->selectedAllowanceUuid);
        if ($allowance && $allowance->balance_due > 0) {
            $this->usd_percentage = round(($this->usd_amount / $allowance->balance_due) * 100, 2);
            $this->local_percentage = 100 - $this->usd_percentage;
        }
    }

    public function updatedLocalCurrencyId()
    {
        $this->local_exchangerate_id = null;
        $this->local_exchange_rate_used = null;
        $this->calculateSplitAmounts();
    }

    public function updatedLocalExchangerateId()
    {
        if ($this->local_exchangerate_id) {
            $rate = Exchangerate::find($this->local_exchangerate_id);
            $this->local_exchange_rate_used = $rate->value;
            $this->calculateSplitAmounts();
        } else {
            $this->local_exchange_rate_used = null;
            $this->local_amount = 0;
        }
    }

    private function calculateSplitAmounts()
    {
        $allowance = $this->getAllowanceByUuid($this->selectedAllowanceUuid);
        if (! $allowance) {
            return;
        }

        $balanceDue = $allowance->balance_due;

        // Calculate USD amount based on percentage
        $this->usd_amount = round($balanceDue * ($this->usd_percentage / 100), 2);

        // Calculate local amount based on remaining USD and exchange rate
        $remainingUsd = round($balanceDue * ($this->local_percentage / 100), 2);

        if ($this->local_exchange_rate_used) {
            $this->local_amount = round($remainingUsd * $this->local_exchange_rate_used, 2);
        } else {
            $this->local_amount = 0;
        }
    }

    public function getLocalCurrencyProperty()
    {
        if (! $this->local_currency_id) {
            return null;
        }

        return Currency::find($this->local_currency_id);
    }

    public function getAvailableLocalExchangeRatesProperty()
    {
        if (! $this->local_currency_id) {
            return collect();
        }

        return Exchangerate::where('secondary_currency_id', $this->local_currency_id)
            ->with(['primarycurrency', 'secondarycurrency', 'user'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function getNonUsdCurrenciesProperty()
    {
        return Currency::where('status', 'active')
            ->where('name', '!=', 'USD')
            ->where('name', '!=', 'US Dollar')
            ->get();
    }

    private function resetSplitPaymentFields()
    {
        $this->usd_amount = 0;
        $this->usd_percentage = 0;
        $this->local_currency_id = null;
        $this->local_exchangerate_id = null;
        $this->local_amount = 0;
        $this->local_percentage = 0;
        $this->local_exchange_rate_used = null;
    }

    // Payment Modal Methods
    public function openPaymentModal($uuid)
    {
        $this->selectedAllowanceUuid = $uuid;
        $allowance = $this->getAllowanceByUuid($uuid);
        $this->selectedAllowanceId = $allowance->id;
        $this->amount_paid = $allowance->balance_due;
        $this->isSplitPayment = false;
        $this->resetSplitPaymentFields();
        $this->paymentmodal = true;
    }

    public function executepayment()
    {
        if ($this->isSplitPayment) {
            $this->executeSplitPayment();
        } else {
            $this->executeSinglePayment();
        }
    }

    private function executeSinglePayment()
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

        $currency = Currency::find($this->currency_id);
        if ($currency->name !== 'USD' && $currency->name !== 'US Dollar') {
            $rules['exchangerate_id'] = 'required|exists:exchangerates,id';
        }

        $this->validate($rules);

        $proofPath = $this->proof_of_payment->store('ts-allowance-payments', 'public');

        $response = $this->tsallowanceService->processpayment($this->selectedAllowanceId, [
            'currency_id' => $this->currency_id,
            'exchangerate_id' => $this->exchangerate_id,
            'amount_paid_usd' => $this->amount_paid_usd ?? $this->amount_paid,
            'amount_paid_original' => $this->amount_paid,
            'exchange_rate_applied' => $this->exchange_rate_used,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'proof_of_payment_path' => $proofPath,
            'payment_notes' => $this->payment_notes,
            'is_split_payment' => false,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->resetPaymentModal();
        } else {
            $this->error($response['message']);
        }
    }

    private function executeSplitPayment()
    {
        $rules = [
            'usd_amount' => 'required|numeric|min:0',
            'usd_percentage' => 'required|numeric|min:0|max:100',
            'local_currency_id' => 'required|exists:currencies,id',
            'local_exchangerate_id' => 'required|exists:exchangerates,id',
            'local_amount' => 'required|numeric|min:0',
            'local_percentage' => 'required|numeric|min:0|max:100',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
            'payment_date' => 'required|date',
            'proof_of_payment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'payment_notes' => 'nullable|string',
        ];

        $this->validate($rules);

        // Validate percentages add up to 100
        if (($this->usd_percentage + $this->local_percentage) != 100) {
            $this->error('USD and local currency percentages must add up to 100%');

            return;
        }

        $proofPath = $this->proof_of_payment->store('ts-allowance-payments', 'public');

        $allowance = $this->getAllowanceByUuid($this->selectedAllowanceUuid);
        $localCurrency = Currency::find($this->local_currency_id);

        // Build split payment details for notes
        $splitDetails = sprintf(
            'Split Payment: USD %.2f (%.0f%%) + %s %.2f (%.0f%%) @ rate %.4f',
            $this->usd_amount,
            $this->usd_percentage,
            $localCurrency->name,
            $this->local_amount,
            $this->local_percentage,
            $this->local_exchange_rate_used
        );

        $response = $this->tsallowanceService->processpayment($this->selectedAllowanceId, [
            'currency_id' => $this->local_currency_id, // Store local currency as primary
            'exchangerate_id' => $this->local_exchangerate_id,
            'amount_paid_usd' => $allowance->balance_due, // Full USD amount covered
            'amount_paid_original' => $this->local_amount, // Local currency amount
            'exchange_rate_applied' => $this->local_exchange_rate_used,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'proof_of_payment_path' => $proofPath,
            'payment_notes' => $splitDetails.($this->payment_notes ? "\n".$this->payment_notes : ''),
            'is_split_payment' => true,
            'split_usd_amount' => $this->usd_amount,
            'split_usd_percentage' => $this->usd_percentage,
            'split_local_amount' => $this->local_amount,
            'split_local_percentage' => $this->local_percentage,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->resetPaymentModal();
        } else {
            $this->error($response['message']);
        }
    }

    private function resetPaymentModal()
    {
        $this->reset([
            'currency_id',
            'exchangerate_id',
            'amount_paid',
            'amount_paid_usd',
            'exchange_rate_used',
            'payment_method',
            'payment_reference',
            'payment_date',
            'proof_of_payment',
            'payment_notes',
            'selectedAllowanceUuid',
            'selectedAllowanceId',
            'isSplitPayment',
        ]);
        $this->resetSplitPaymentFields();
        $this->paymentmodal = false;
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.ts-allowance-payments', [
            'awaitingPaymentAllowances' => $this->getAwaitingPaymentAllowances(),
            'paymentsToday' => $this->getPaymentProcessedToday(),
            'paymentsThisMonth' => $this->getPaymentProcessedThisMonth(),
            'totalPaidThisMonth' => $this->getTotalPaidThisMonth(),
            'totalPaidByCurrency' => $this->getTotalPaidByCurrencyThisMonth(),
        ]);
    }
}
