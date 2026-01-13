<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\icurrencyInterface;
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

    protected $paymentvoucherService;

    protected $currencyrepo;

    protected $bankaccountrepo;

    public function boot(ipaymentvoucherService $paymentvoucherService, icurrencyInterface $currencyrepo, ibankaccountInterface $bankaccountrepo)
    {
        $this->paymentvoucherService = $paymentvoucherService;
        $this->currencyrepo = $currencyrepo;
        $this->bankaccountrepo = $bankaccountrepo;
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
            return [
                'source_type' => $item->source_type,
                'source_id' => $item->source_id,
                'source_line_id' => $item->source_line_id,
                'description' => $item->description,
                'original_currency' => $item->original_currency,
                'original_amount' => $item->original_amount,
                'edited_amount' => $item->edited_amount,
                'amount_change_comment' => $item->amount_change_comment,
                'account_type' => $item->account_type,
                'gl_code' => $item->gl_code,
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
                'edited_amount' => null,
                'amount_change_comment' => null,
                'account_type' => null,
                'gl_code' => null,
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
        $total = collect($this->selectedItems)->sum(function ($item) {
            return $item['edited_amount'] ?? $item['original_amount'];
        });

        // Only apply exchange rate if:
        // 1. Currency is ZiG
        // 2. Exchange rate is provided
        // 3. There are items in different currency (need conversion)
        if ($this->currency === 'ZiG' && $this->exchange_rate) {
            $hasDifferentCurrency = collect($this->selectedItems)->contains(function ($item) {
                return $item['original_currency'] !== 'ZiG';
            });

            if ($hasDifferentCurrency) {
                $total = $total * $this->exchange_rate;
            }
        }

        return $total;
    }

    public function updatedSelectedItems($value, $key)
    {
        // Reset edited amount and comment when item is removed
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            if (count($parts) === 2) {
                $index = $parts[0];
                $field = $parts[1];
                if ($field === 'edited_amount' && empty($value)) {
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

        // Validate edited amounts have comments if changed
        foreach ($this->selectedItems as $index => $item) {
            if (isset($item['edited_amount']) && $item['edited_amount'] != $item['original_amount']) {
                if (empty($item['amount_change_comment'])) {
                    $this->error("Comment is required for amount change on item: {$item['description']}");

                    return;
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
