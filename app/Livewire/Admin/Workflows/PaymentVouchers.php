<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Interfaces\repositories\istaffwelfareloanInterface;
use App\Interfaces\repositories\itsallowanceInterface;
use App\Interfaces\services\ipaymentvoucherService;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class PaymentVouchers extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $search;

    public $year;

    public $modal = false;

    public $id;

    public $voucher_date;

    public $currency;

    public $exchange_rate;

    public $bank_account_id;

    public $selectedItems = [];

    public $eligibleItems = [];

    public $viewItemModal = false;

    public $viewedItemDetails = null;

    public $viewedItemSourceType = null;

    public $viewedItemLineId = null;

    protected $paymentvoucherService;

    protected $currencyrepo;

    protected $bankaccountrepo;

    protected $paymentrequisitionrepo;

    protected $tsallowancerepo;

    protected $staffwelfareloanrepo;

    public function boot(
        ipaymentvoucherService $paymentvoucherService,
        icurrencyInterface $currencyrepo,
        ibankaccountInterface $bankaccountrepo,
        ipaymentrequisitionInterface $paymentrequisitionrepo,
        itsallowanceInterface $tsallowancerepo,
        istaffwelfareloanInterface $staffwelfareloanrepo
    ) {
        $this->paymentvoucherService = $paymentvoucherService;
        $this->currencyrepo = $currencyrepo;
        $this->bankaccountrepo = $bankaccountrepo;
        $this->paymentrequisitionrepo = $paymentrequisitionrepo;
        $this->tsallowancerepo = $tsallowancerepo;
        $this->staffwelfareloanrepo = $staffwelfareloanrepo;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Vouchers'],
        ];
        $this->year = date('Y');
        $this->search = '';
        $this->voucher_date = now()->toDateString();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCurrency()
    {
        if ($this->currency !== 'ZiG') {
            $this->exchange_rate = null;
        } else {
            // Check if exchange rate is needed (items in different currency)
            $hasDifferentCurrency = collect($this->selectedItems)->contains(function ($item) {
                return $item['original_currency'] !== 'ZiG';
            });
            // Exchange rate is optional even for ZiG if all items are already in ZiG
        }
    }

    public function getvouchers()
    {
        return $this->paymentvoucherService->getvouchers($this->year, $this->search);
    }

    public function getcurrencies()
    {
        return $this->currencyrepo->getcurrencies()->filter(function ($currency) {
            return strtoupper($currency->status) === 'ACTIVE';
        })->values();
    }

    public function getbankaccounts()
    {
        return $this->bankaccountrepo->getbankaccounts()->filter(function ($account) {
            return strtolower($account->account_status ?? 'active') === 'active';
        })->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->account_number.' - '.($account->currency->name ?? '').' ('.$account->account_type.')',
            ];
        })->values();
    }

    public function geteligibleitems()
    {
        $this->eligibleItems = $this->paymentvoucherService->geteligibleitems($this->year);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->geteligibleitems();
        $this->modal = true;
    }

    public function edit($id)
    {
        $voucher = $this->paymentvoucherService->getvoucher($id);

        if (! $voucher) {
            $this->error('Payment Voucher Not Found');

            return;
        }

        if ($voucher->status !== 'DRAFT') {
            $this->error('Only Draft Vouchers Can Be Edited');

            return;
        }

        $this->id = $voucher->id;
        $this->voucher_date = $voucher->voucher_date->format('Y-m-d');
        $this->currency = $voucher->currency;
        $this->bank_account_id = $voucher->bank_account_id;
        $this->exchange_rate = $voucher->exchange_rate;
        $this->selectedItems = $voucher->items->map(function ($item) {
            // Recalculate remaining balance when editing
            $remainingBalance = $item->original_amount;
            if ($item->source_line_id) {
                // Calculate total paid for this line item
                $totalPaid = \App\Models\PaymentVoucherItem::where('source_type', $item->source_type)
                    ->where('source_id', $item->source_id)
                    ->where('source_line_id', $item->source_line_id)
                    ->where('id', '!=', $item->id) // Exclude current item
                    ->sum('payable_amount');
                $remainingBalance = $item->original_amount - $totalPaid;
            }

            return [
                'source_type' => $item->source_type,
                'source_id' => $item->source_id,
                'source_line_id' => $item->source_line_id,
                'description' => $item->description,
                'original_currency' => $item->original_currency,
                'original_amount' => $item->original_amount,
                'total_paid' => 0, // Will be recalculated when needed
                'remaining_balance' => $remainingBalance,
                'amount_change' => $item->edited_amount, // edited_amount represents amount change
                'amount_change_comment' => $item->amount_change_comment,
                'partial_amount' => null, // Partial payment is stored in payable_amount but we'll handle separately
                'account_type' => $item->account_type,
                'gl_code' => $item->gl_code,
                'payee_regnumber' => $item->payee_regnumber,
                'payee_name' => $item->payee_name,
                'apply_rate_to_zig' => false, // Default to false when editing
            ];
        })->toArray();

        $this->geteligibleitems();
        $this->modal = true;
    }

    public function toggleItem($index)
    {
        $item = $this->eligibleItems[$index];
        $key = $item['source_type'].'-'.$item['source_id'].'-'.($item['source_line_id'] ?? 'null');

        $existingIndex = collect($this->selectedItems)->search(function ($selected) use ($item) {
            return $selected['source_type'] === $item['source_type']
                && $selected['source_id'] === $item['source_id']
                && ($selected['source_line_id'] ?? null) === ($item['source_line_id'] ?? null);
        });

        if ($existingIndex !== false) {
            unset($this->selectedItems[$existingIndex]);
            $this->selectedItems = array_values($this->selectedItems);
        } else {
            $this->selectedItems[] = [
                'source_type' => $item['source_type'],
                'source_id' => $item['source_id'],
                'source_line_id' => $item['source_line_id'] ?? null,
                'description' => $item['description'],
                'original_currency' => $item['original_currency'],
                'original_amount' => $item['original_amount'],
                'total_paid' => $item['total_paid'] ?? 0,
                'remaining_balance' => $item['remaining_balance'] ?? $item['original_amount'],
                'amount_change' => null, // Amount change field (to change original amount, requires comment)
                'amount_change_comment' => null,
                'partial_amount' => null, // Partial payment amount (optional, after amount change)
                'account_type' => null,
                'gl_code' => null,
                'payee_regnumber' => $item['payee_regnumber'] ?? null,
                'payee_name' => $item['payee_name'] ?? null,
                'apply_rate_to_zig' => false, // Default to false for ZiG items
            ];
        }
    }

    public function isItemSelected($item)
    {
        return collect($this->selectedItems)->contains(function ($selected) use ($item) {
            return $selected['source_type'] === $item['source_type']
                && $selected['source_id'] === $item['source_id']
                && ($selected['source_line_id'] ?? null) === ($item['source_line_id'] ?? null);
        });
    }

    public function getTotalAmountProperty()
    {
        $total = 0;
        $exchangeRate = is_numeric($this->exchange_rate) ? (float) $this->exchange_rate : 0;

        foreach ($this->selectedItems as $item) {
            $originalAmount = (float) ($item['original_amount'] ?? 0);
            $remainingBalance = (float) ($item['remaining_balance'] ?? $originalAmount);
            $amountChange = null;
            $partialAmount = null;

            // Get amount change (changes the original amount)
            if (isset($item['amount_change']) && $item['amount_change'] !== null && $item['amount_change'] !== '') {
                $amountChange = is_numeric($item['amount_change']) ? (float) $item['amount_change'] : null;
            }

            // Get partial payment amount (optional, after amount change)
            if (isset($item['partial_amount']) && $item['partial_amount'] !== null && $item['partial_amount'] !== '') {
                $partialAmount = is_numeric($item['partial_amount']) ? (float) $item['partial_amount'] : null;
            }

            // Determine the payment amount:
            // 1. If amount change is set, use it as the base (unless partial amount is also set)
            // 2. If partial amount is set, use it (must be <= changed amount or remaining balance)
            // 3. Otherwise, use remaining balance
            $paymentAmount = $remainingBalance;

            if ($amountChange !== null) {
                // Amount has been changed, use changed amount as base
                $changedAmount = $amountChange;

                if ($partialAmount !== null) {
                    // Partial payment on the changed amount
                    $paymentAmount = min($partialAmount, $changedAmount);
                } else {
                    // Full payment of the changed amount
                    $paymentAmount = $changedAmount;
                }
            } elseif ($partialAmount !== null) {
                // Partial payment without amount change
                $paymentAmount = min($partialAmount, $remainingBalance);
            }

            // Calculate payable amount based on currency and exchange rate logic
            if ($this->currency === 'ZiG' && $exchangeRate > 0) {
                // If item currency is USD, always apply exchange rate
                if (strtoupper($item['original_currency'] ?? '') === 'USD') {
                    $total += $paymentAmount * $exchangeRate;
                }
                // If item currency is ZiG, apply rate only if user selected to apply it
                elseif (strtoupper($item['original_currency'] ?? '') === 'ZIG') {
                    $applyRate = $item['apply_rate_to_zig'] ?? false;
                    if ($applyRate) {
                        $total += $paymentAmount * $exchangeRate;
                    } else {
                        $total += $paymentAmount;
                    }
                }
                // For other currencies, apply exchange rate
                else {
                    $total += $paymentAmount * $exchangeRate;
                }
            } else {
                // If voucher currency is not ZiG or no exchange rate, use amount as is
                $total += $paymentAmount;
            }
        }

        return $total;
    }

    public function updatedSelectedItems($value, $key)
    {
        // Reset comment when amount change is cleared
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            if (count($parts) === 2) {
                $index = $parts[0];
                $field = $parts[1];
                if ($field === 'amount_change' && empty($value)) {
                    if (isset($this->selectedItems[$index])) {
                        $this->selectedItems[$index]['amount_change_comment'] = null;
                    }
                }
            }
        }
    }

    public function save()
    {
        // Check if exchange rate is needed
        $needsExchangeRate = false;
        if ($this->currency === 'ZiG') {
            $hasDifferentCurrency = collect($this->selectedItems)->contains(function ($item) {
                return $item['original_currency'] !== 'ZiG';
            });
            $needsExchangeRate = $hasDifferentCurrency;
        }

        // Validate amount changes and partial payments
        foreach ($this->selectedItems as $index => $item) {
            $remainingBalance = (float) ($item['remaining_balance'] ?? $item['original_amount']);
            $originalAmount = (float) ($item['original_amount'] ?? 0);
            $amountChange = null;
            $partialAmount = null;

            // Get amount change
            if (isset($item['amount_change']) && $item['amount_change'] !== null && $item['amount_change'] !== '') {
                $amountChange = is_numeric($item['amount_change']) ? (float) $item['amount_change'] : null;

                if ($amountChange !== null) {
                    // Check if amount change is negative
                    if ($amountChange < 0) {
                        $this->error("Amount change cannot be negative for item: {$item['description']}");

                        return;
                    }

                    // Check if amount change differs from original amount (requires comment)
                    if (abs($amountChange - $originalAmount) > 0.01 && empty($item['amount_change_comment'])) {
                        $this->error("Comment is required when amount differs from original amount ({$originalAmount}) for item: {$item['description']}");

                        return;
                    }
                }
            }

            // Get partial payment amount
            if (isset($item['partial_amount']) && $item['partial_amount'] !== null && $item['partial_amount'] !== '') {
                $partialAmount = is_numeric($item['partial_amount']) ? (float) $item['partial_amount'] : null;

                if ($partialAmount !== null) {
                    // Check if partial amount is negative
                    if ($partialAmount < 0) {
                        $this->error("Partial amount cannot be negative for item: {$item['description']}");

                        return;
                    }

                    // Determine the maximum allowed partial amount
                    $maxPartialAmount = $amountChange !== null ? $amountChange : $remainingBalance;

                    // Check if partial amount exceeds the maximum allowed
                    if ($partialAmount > $maxPartialAmount) {
                        $maxLabel = $amountChange !== null ? "changed amount ({$amountChange})" : "remaining balance ({$remainingBalance})";
                        $this->error("Partial amount ({$partialAmount}) cannot exceed {$maxLabel} for item: {$item['description']}");

                        return;
                    }
                }
            }
        }

        $this->validate([
            'voucher_date' => 'required|date',
            'currency' => 'required|string',
            'bank_account_id' => 'required|exists:bankaccounts,id',
            'exchange_rate' => $needsExchangeRate ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*.account_type' => 'required|string',
            'selectedItems.*.gl_code' => 'required|string',
        ], [
            'bank_account_id.required' => 'Bank account is required',
            'bank_account_id.exists' => 'Selected bank account is invalid',
            'exchange_rate.required' => 'Exchange rate is required when converting from different currency to ZiG',
            'selectedItems.min' => 'At least one item must be selected',
            'selectedItems.*.account_type.required' => 'Account type is required for all items',
            'selectedItems.*.gl_code.required' => 'GL Code is required for all items',
        ]);

        $data = [
            'voucher_date' => $this->voucher_date,
            'currency' => $this->currency,
            'bank_account_id' => $this->bank_account_id,
            'exchange_rate' => $this->exchange_rate,
            'items' => $this->selectedItems,
        ];

        if ($this->id) {
            $response = $this->paymentvoucherService->updatevoucher($this->id, $data);
        } else {
            $response = $this->paymentvoucherService->createvoucher($data);
        }

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->resetForm();
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->paymentvoucherService->deletevoucher($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function submit($id)
    {
        $response = $this->paymentvoucherService->submitvoucher($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function resetForm()
    {
        $this->reset(['id', 'voucher_date', 'currency', 'bank_account_id', 'exchange_rate', 'selectedItems']);
        $this->voucher_date = now()->toDateString();
        $this->selectedItems = [];
    }

    public function viewItemDetails($index)
    {
        $item = $this->eligibleItems[$index];
        $this->viewedItemSourceType = $item['source_type'];
        $this->viewedItemDetails = null;
        $this->viewedItemLineId = $item['source_line_id'] ?? null;

        try {
            switch ($item['source_type']) {
                case 'PAYMENT_REQUISITION':
                    // Get payment requisition by ID first to get UUID, then get full details
                    $pr = $this->paymentrequisitionrepo->getpaymentrequisition($item['source_id']);
                    if ($pr && $pr->uuid) {
                        $this->viewedItemDetails = $this->paymentrequisitionrepo->getpaymentrequisitionbyuuid($pr->uuid);
                    } else {
                        $this->error('Payment requisition not found');

                        return;
                    }
                    break;

                case 'TNS':
                    // Get TNS allowance by ID first to get UUID, then get full details
                    $ts = $this->tsallowancerepo->getallowance($item['source_id']);
                    if ($ts && $ts->uuid) {
                        $this->viewedItemDetails = $this->tsallowancerepo->getallowancebyuuid($ts->uuid);
                    } else {
                        $this->error('T&S allowance not found');

                        return;
                    }
                    break;

                case 'STAFF_WELFARE':
                    // Get staff welfare loan by ID first to get UUID, then get full details
                    $loan = $this->staffwelfareloanrepo->getloan($item['source_id']);
                    if ($loan && $loan->uuid) {
                        $this->viewedItemDetails = $this->staffwelfareloanrepo->getloanbyuuid($loan->uuid);
                    } else {
                        $this->error('Staff welfare loan not found');

                        return;
                    }
                    break;

                default:
                    $this->error('Unknown source type');

                    return;
            }

            if (! $this->viewedItemDetails) {
                $this->error('Item details not found');

                return;
            }

            $this->viewItemModal = true;
        } catch (\Exception $e) {
            $this->error('Error loading item details: '.$e->getMessage());
        }
    }

    public function closeViewItemModal()
    {
        $this->viewItemModal = false;
        $this->viewedItemDetails = null;
        $this->viewedItemSourceType = null;
        $this->viewedItemLineId = null;
    }

    public function headers(): array
    {
        return [
            ['key' => 'voucher_number', 'label' => 'Voucher Number'],
            ['key' => 'voucher_date', 'label' => 'Date'],
            ['key' => 'currency', 'label' => 'Currency'],
            ['key' => 'total_amount', 'label' => 'Total Amount'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => 'Action'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.payment-vouchers', [
            'vouchers' => $this->getvouchers(),
            'currencies' => $this->getcurrencies(),
            'bankAccounts' => $this->getbankaccounts(),
            'headers' => $this->headers(),
        ]);
    }
}
