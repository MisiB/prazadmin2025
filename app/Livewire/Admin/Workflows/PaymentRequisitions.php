<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\iuserInterface;
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

    public $payee_type = 'CUSTOMER';

    public $payee_id;

    public $payee_regnumber;

    public $payee_name;

    public $payee_search = '';

    public $selectedCustomer = null;

    public $selectedUser = null;

    protected $paymentrequisitionService;

    protected $budgetrepo;

    protected $currencyrepo;

    protected $customerrepo;

    protected $userrepo;

    public function boot(ipaymentrequisitionService $paymentrequisitionService, ibudgetInterface $budgetrepo, icurrencyInterface $currencyrepo, icustomerInterface $customerrepo, iuserInterface $userrepo)
    {
        $this->paymentrequisitionService = $paymentrequisitionService;
        $this->budgetrepo = $budgetrepo;
        $this->currencyrepo = $currencyrepo;
        $this->customerrepo = $customerrepo;
        $this->userrepo = $userrepo;
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

    public function updatedPayeeType($value): void
    {
        $this->payee_id = null;
        $this->payee_regnumber = null;
        $this->payee_name = null;
        $this->payee_search = '';
        $this->selectedCustomer = null;
        $this->selectedUser = null;
    }

    public function updatedPayeeSearch($value): void
    {
        if (empty($value)) {
            $this->selectedCustomer = null;
            $this->selectedUser = null;
            $this->payee_id = null;
            $this->payee_regnumber = null;
            $this->payee_name = null;

            return;
        }

        if ($this->payee_type === 'CUSTOMER') {
            $customer = $this->customerrepo->getCustomerByRegnumber($value);
            if ($customer) {
                $this->selectedCustomer = $customer;
                $this->payee_id = $customer->id;
                $this->payee_regnumber = $customer->regnumber;
                $this->payee_name = $customer->name;
            } else {
                $this->selectedCustomer = null;
                $this->payee_id = null;
                $this->payee_regnumber = null;
                $this->payee_name = null;
            }
        } elseif ($this->payee_type === 'USER') {
            $user = $this->userrepo->getusers($value)->first();
            if ($user) {
                $this->selectedUser = $user;
                $this->payee_id = $user->id;
                $this->payee_regnumber = $user->name;
                $this->payee_name = $user->name;
            } else {
                $this->selectedUser = null;
                $this->payee_id = null;
                $this->payee_regnumber = null;
                $this->payee_name = null;
            }
        }
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
        $this->payee_type = $paymentrequisition->payee_type ?? 'CUSTOMER';
        $this->payee_regnumber = $paymentrequisition->payee_regnumber;
        $this->payee_name = $paymentrequisition->payee_name;

        // Set payee search and selected objects based on type
        if ($this->payee_type === 'CUSTOMER' && $paymentrequisition->payeeCustomer) {
            $this->selectedCustomer = $paymentrequisition->payeeCustomer;
            $this->payee_id = $paymentrequisition->payeeCustomer->id;
            $this->payee_search = $paymentrequisition->payee_regnumber;
        } elseif ($this->payee_type === 'USER' && $paymentrequisition->payeeUser) {
            $this->selectedUser = $paymentrequisition->payeeUser;
            $this->payee_id = $paymentrequisition->payeeUser->id;
            $this->payee_search = $paymentrequisition->payee_regnumber;
        }

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
            'payee_type' => 'required|in:CUSTOMER,USER',
            'payee_regnumber' => 'required|string',
            'payee_name' => 'required|string',
            'lineItems' => 'required|array|min:1',
            'lineItems.*.quantity' => 'required|numeric|min:1',
            'lineItems.*.description' => 'required|string',
            'lineItems.*.unit_amount' => 'required|numeric|min:0',
            'invoice_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'tax_clearance_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'other_attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        $validationMessages = [
            'payee_type.required' => 'Payee type is required',
            'payee_regnumber.required' => 'Please search and select a payee',
            'payee_name.required' => 'Please search and select a payee',
            'lineItems.required' => 'At least one line item is required',
            'lineItems.min' => 'At least one line item is required',
            'invoice_file.required' => 'Invoice file is required',
            'tax_clearance_file.required' => 'Tax clearance file is required',
        ];

        // Validate payee selection
        if ($this->payee_type === 'CUSTOMER' && ! $this->selectedCustomer) {
            $this->error('Please search and select a customer');

            return;
        }

        if ($this->payee_type === 'USER' && ! $this->selectedUser) {
            $this->error('Please search and select a user/staff');

            return;
        }

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
            'payee_type' => $this->payee_type,
            'payee_regnumber' => $this->payee_regnumber,
            'payee_name' => $this->payee_name,
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
            'payee_type' => $this->payee_type,
            'payee_regnumber' => $this->payee_regnumber,
            'payee_name' => $this->payee_name,
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
        $this->reset(['id', 'source_id', 'budget_line_item_id', 'purpose', 'currency_id', 'total_amount', 'lineItems', 'maxbudget', 'availableQuantity', 'invoice_file', 'tax_clearance_file', 'other_attachments', 'payee_id', 'payee_regnumber', 'payee_name', 'payee_search']);
        $this->payee_type = 'CUSTOMER';
        $this->selectedCustomer = null;
        $this->selectedUser = null;
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
