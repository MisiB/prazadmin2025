<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\services\ipaymentrequisitionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class PaymentRequisitions extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public $breadcrumbs = [];

    public $search;

    public $year;

    public $modal;

    public $id;

    public $source_type = 'USER';

    public $source_id;

    public $budget_id;

    public $budget_line_item_id;

    public $purpose;

    public $department_id;

    public $currency_id;

    public $total_amount = 0;

    public $lineItems = [];

    public $maxbudget = 0;

    public $availableQuantity = 0;

    public $invoice_file;

    public $tax_clearance_file;

    public $other_attachments = [];

    protected $paymentrequisitionService;

    protected $budgetrepo;

    protected $currencyrepo;

    public function boot(ipaymentrequisitionService $paymentrequisitionService, ibudgetInterface $budgetrepo, icurrencyInterface $currencyrepo)
    {
        $this->paymentrequisitionService = $paymentrequisitionService;
        $this->budgetrepo = $budgetrepo;
        $this->currencyrepo = $currencyrepo;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Requisitions'],
        ];
        $this->year = date('Y');
        $this->search = '';
        $this->getbudgets();
        $this->getbudgetitems();
        $this->addLineItem();
    }

    public function getbudgets()
    {
        $budgets = $this->budgetrepo->getbudgets();
        $budget = $budgets->where('year', $this->year)->first();
        $this->budget_id = $budget->id ?? null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getbudgetitems()
    {
        if (! $this->budget_id) {
            return collect();
        }

        return $this->budgetrepo->getbudgetitemsbydepartment($this->budget_id, Auth::user()->department->department_id);
    }

    public function getcurrencies()
    {
        return $this->currencyrepo->getcurrencies()->filter(function ($currency) {
            return strtoupper($currency->status) === 'ACTIVE';
        })->values();
    }

    public function getpaymentrequisitions()
    {
        return $this->paymentrequisitionService->getpaymentrequisitionsbyapplicant(Auth::user()->id, $this->year, $this->search);
    }

    public function updatedBudgetLineItemId($value): void
    {
        if (empty($value)) {
            $this->maxbudget = 0;
            $this->availableQuantity = 0;
            $this->currency_id = null;

            return;
        }

        $budgetitem = $this->getbudgetitems()->where('id', $value)->first();

        if (! $budgetitem) {
            $this->maxbudget = 0;
            $this->availableQuantity = 0;
            $this->currency_id = null;

            return;
        }

        $this->currency_id = $budgetitem->currency_id;
        $this->department_id = $budgetitem->department_id;

        // Calculate available budget
        $budgetitem_amount = $budgetitem->total;
        $budgetitem_outgoingvirements = $budgetitem->outgoingvirements()->sum('amount');
        $budgetitem_incomingvirements = $budgetitem->incomingvirements()->sum('amount');
        $budgetitem_purchaserequisitions = $budgetitem->purchaserequisitions()->whereNotIn('status', ['DRAFT'])->sum('quantity') * $budgetitem->unitprice;
        $this->maxbudget = $budgetitem_amount - $budgetitem_outgoingvirements + $budgetitem_incomingvirements - $budgetitem_purchaserequisitions;

        // Calculate available quantity
        $budgetitem_quantity = $budgetitem->quantity ?? 0;
        $budgetitem_used_quantity = $budgetitem->purchaserequisitions()->whereNotIn('status', ['DRAFT'])->sum('quantity');
        $this->availableQuantity = max(0, $budgetitem_quantity - $budgetitem_used_quantity);
    }

    public function addLineItem()
    {
        $this->lineItems[] = [
            'quantity' => 1,
            'description' => '',
            'unit_amount' => 0,
            'line_total' => 0,
        ];
        $this->calculateTotal();
    }

    public function removeLineItem($index)
    {
        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems);
        $this->calculateTotal();
    }

    public function updatedLineItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) == 2) {
            $index = $parts[0];
            $field = $parts[1];

            if ($field == 'quantity' || $field == 'unit_amount') {
                $quantity = (float) ($this->lineItems[$index]['quantity'] ?? 0);
                $unitAmount = (float) ($this->lineItems[$index]['unit_amount'] ?? 0);
                $this->lineItems[$index]['line_total'] = $quantity * $unitAmount;
                $this->calculateTotal();
            }
        }
    }

    public function calculateTotal()
    {
        $this->total_amount = collect($this->lineItems)->sum('line_total');
    }

    public function edit($id)
    {
        $this->id = $id;
        $paymentrequisition = $this->paymentrequisitionService->getpaymentrequisition($id);
        $this->source_type = $paymentrequisition->source_type;
        $this->source_id = $paymentrequisition->source_id;
        $this->budget_id = $paymentrequisition->budget_id;
        $this->budget_line_item_id = $paymentrequisition->budget_line_item_id;
        $this->purpose = $paymentrequisition->purpose;
        $this->department_id = $paymentrequisition->department_id;
        $this->currency_id = $paymentrequisition->currency_id;
        $this->total_amount = $paymentrequisition->total_amount;

        $this->lineItems = $paymentrequisition->lineItems->map(function ($item) {
            return [
                'quantity' => $item->quantity,
                'description' => $item->description,
                'unit_amount' => $item->unit_amount,
                'line_total' => $item->line_total,
            ];
        })->toArray();

        $this->updatedBudgetLineItemId($this->budget_line_item_id);
        $this->modal = true;
    }

    public function save()
    {
        $validationRules = [
            'budget_line_item_id' => 'required',
            'purpose' => 'required|string',
            'currency_id' => 'required',
            'lineItems' => 'required|array|min:1',
            'lineItems.*.quantity' => 'required|numeric|min:1',
            'lineItems.*.description' => 'required|string',
            'lineItems.*.unit_amount' => 'required|numeric|min:0',
            'invoice_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'tax_clearance_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'other_attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        $validationMessages = [
            'lineItems.required' => 'At least one line item is required',
            'lineItems.min' => 'At least one line item is required',
            'invoice_file.required' => 'Invoice file is required',
            'tax_clearance_file.required' => 'Tax clearance file is required',
        ];

        $this->validate($validationRules, $validationMessages);

        // Currency check
        $selectedCurrency = $this->getcurrencies()->where('id', $this->currency_id)->first();
        $isZigCurrency = $selectedCurrency && strtoupper($selectedCurrency->name) === 'ZIG';

        // Quantity validation - always validate against available quantity
        $totalQuantity = collect($this->lineItems)->sum('quantity');
        if ($totalQuantity > $this->availableQuantity) {
            $this->error('Total quantity ('.$totalQuantity.') exceeds available quantity ('.$this->availableQuantity.')');

            return;
        }

        // Budget amount validation - only for non-ZiG currencies
        if (! $isZigCurrency && $this->total_amount > $this->maxbudget) {
            $this->error('Total amount exceeds available budget');

            return;
        }

        if ($this->id != null) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function create()
    {
        // Handle file uploads
        $attachments = [];

        if ($this->invoice_file) {
            $attachments['invoice'] = $this->invoice_file->store('payment-requisition-documents', 'public');
        }

        if ($this->tax_clearance_file) {
            $attachments['tax_clearance'] = $this->tax_clearance_file->store('payment-requisition-documents', 'public');
        }

        if (! empty($this->other_attachments)) {
            foreach ($this->other_attachments as $index => $file) {
                if ($file) {
                    $attachments['other_'.$index] = $file->store('payment-requisition-documents', 'public');
                }
            }
        }

        $response = $this->paymentrequisitionService->createpaymentrequisition([
            // source_type and source_id will be set automatically in repository (USER for manual creation)
            'budget_id' => $this->budget_id,
            'budget_line_item_id' => $this->budget_line_item_id,
            'purpose' => $this->purpose,
            'department_id' => $this->department_id,
            'currency_id' => $this->currency_id,
            'line_items' => $this->lineItems,
            'attachments' => $attachments,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->resetForm();
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function update()
    {
        $response = $this->paymentrequisitionService->updatepaymentrequisition($this->id, [
            'budget_id' => $this->budget_id,
            'budget_line_item_id' => $this->budget_line_item_id,
            'purpose' => $this->purpose,
            'department_id' => $this->department_id,
            'currency_id' => $this->currency_id,
            'line_items' => $this->lineItems,
        ]);

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
        $response = $this->paymentrequisitionService->deletepaymentrequisition($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function submit($id)
    {
        $response = $this->paymentrequisitionService->submitpaymentrequisition($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function resetForm()
    {
        $this->reset(['id', 'source_id', 'budget_line_item_id', 'purpose', 'currency_id', 'total_amount', 'lineItems', 'maxbudget', 'availableQuantity', 'invoice_file', 'tax_clearance_file', 'other_attachments']);
        $this->other_attachments = [];
        $this->addLineItem();
    }

    public function addOtherAttachment()
    {
        $this->other_attachments[] = null;
    }

    public function removeOtherAttachment($index)
    {
        unset($this->other_attachments[$index]);
        $this->other_attachments = array_values($this->other_attachments);
    }

    public function getSelectedCurrencyProperty()
    {
        if (! $this->currency_id) {
            return null;
        }

        return $this->getcurrencies()->where('id', $this->currency_id)->first();
    }

    public function getIsZigCurrencyProperty()
    {
        $currency = $this->selectedCurrency;

        return $currency && strtoupper($currency->name) === 'ZIG';
    }

    public function headers(): array
    {
        return [
            ['key' => 'reference_number', 'label' => 'Reference'],
            ['key' => 'purpose', 'label' => 'Purpose'],
            ['key' => 'total_amount', 'label' => 'Total Amount'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.payment-requisitions', [
            'breadcrumbs' => $this->breadcrumbs,
            'paymentrequisitions' => $this->getpaymentrequisitions(),
            'budgetitems' => $this->getbudgetitems(),
            'currencies' => $this->getcurrencies(),
            'headers' => $this->headers(),
        ]);
    }
}
