<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Awaitingpmu extends Component
{
    use Toast,WithFileUploads,WithPagination;

    public $search;

    public $year;

    protected $repository;

    protected $customerrepo;

    protected $currencyrepo;

    public $breadcrumbs;

    public $purchaserequisition = null;

    public bool $modal = false;

    public bool $awardmodal = false;

    public $customer_id;

    public $customer;

    public $tendernumber;

    public $quantity;

    public $amount;

    public $status;

    public $item;

    public $regnumber;

    public $id;

    public $currency_id;

    public $payment_currency_id;

    public $is_split_payment = false;

    public $second_payment_currency_id;

    public $second_payment_amount;

    public $pay_at_prevailing_rate = false;

    public $currencies;

    public bool $documentmodal = false;

    public $documents;

    public $purchaserequisitionaward_id;

    public $purchaserequisitionawarddocument_id;

    public $file;

    public $document;

    public $currentdocument;

    public bool $awarddocumentmodal = false;

    public bool $viewdocumentmodal = false;

    public function mount()
    {
        $this->year = date('Y');
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Procurement'],
        ];
        $this->documents = new Collection;
        $this->currencies = $this->currencyrepo->getcurrencies();
    }

    public function boot(ipurchaseerequisitionInterface $repository, icustomerInterface $customerrepo, icurrencyInterface $currencyrepo)
    {
        $this->repository = $repository;
        $this->customerrepo = $customerrepo;
        $this->currencyrepo = $currencyrepo;
    }

    public function documentlist(): array
    {
        return [
            ['id' => 'Quotation', 'name' => 'Quotation'],
            ['id' => 'Evaluation Report', 'name' => 'Evaluation Report'],
            ['id' => 'Contract', 'name' => 'Contract'],
            ['id' => 'Purchase Order', 'name' => 'Purchase Order'],
            ['id' => 'Award Notice', 'name' => 'Award Notice'],
        ];
    }

    public function getawaitingpmu()
    {
        return $this->repository->getpurchaseerequisitionbystatus($this->year, 'AWAITING_PMU');
    }

    public function UpdatedRegnumber()
    {
        $customer = $this->customerrepo->getCustomerByRegnumber($this->regnumber);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer = $customer;
        }

    }

    public function updatedPaymentCurrencyId()
    {
        // Reset pay_at_prevailing_rate if currency is not ZiG
        $selectedCurrency = $this->currencies->firstWhere('id', $this->payment_currency_id);
        if ($selectedCurrency && strtoupper($selectedCurrency->name) !== 'ZIG') {
            $this->pay_at_prevailing_rate = false;
        }
    }

    public function updatedIsSplitPayment()
    {
        // Clear second payment fields if split payment is disabled
        if (! $this->is_split_payment) {
            $this->second_payment_currency_id = null;
            $this->second_payment_amount = null;
        }
    }

    public function getIsZigCurrencyProperty()
    {
        if (! $this->payment_currency_id) {
            return false;
        }
        $selectedCurrency = $this->currencies->firstWhere('id', $this->payment_currency_id);

        return $selectedCurrency && strtoupper($selectedCurrency->name) === 'ZIG';
    }

    public function openAwardModal()
    {
        // Initialize payment currency to budget item's currency for new awards
        if ($this->purchaserequisition && ! $this->id) {
            $this->payment_currency_id = $this->purchaserequisition->budgetitem->currency_id ?? null;
        }
        $this->awardmodal = true;
    }

    public function getdocuments($id)
    {
        $this->documents = $this->repository->getawarddocuments($id);
        $this->documentmodal = true;
        $this->purchaserequisitionaward_id = $id;
    }

    public function save()
    {
        $this->validate([
            'customer_id' => 'required',
            'tendernumber' => 'required',
            'item' => 'required',
            'quantity' => 'required|numeric',
            'amount' => 'required',
            'payment_currency_id' => 'required',
            'is_split_payment' => 'boolean',
            'second_payment_currency_id' => 'required_if:is_split_payment,true',
            'pay_at_prevailing_rate' => 'boolean',
        ]);
        $maxquantity = $this->computequantitylimit();
        if ($this->quantity > $maxquantity) {
            $this->error('Quantity cannot exceed the Purchase Requisition Quantity');

            return;
        }
        if ($this->id) {
            $this->update();
        } else {
            $this->create();
        }
        $this->reset([
            'customer_id',
            'tendernumber',
            'customer',
            'regnumber',
            'item',
            'quantity',
            'amount',
            'currency_id',
            'payment_currency_id',
            'is_split_payment',
            'second_payment_currency_id',
            'second_payment_amount',
            'pay_at_prevailing_rate',
        ]);
        $this->awardmodal = false;
    }

    public function create()
    {
        $data = [
            'purchaserequisition_id' => $this->purchaserequisition->id,
            'customer_id' => $this->customer_id,
            'tendernumber' => $this->tendernumber,
            'item' => $this->item,
            'quantity' => $this->quantity,
            'currency_id' => $this->purchaserequisition->budgetitem->currency_id,
            'amount' => $this->amount,
            'year' => $this->year,
            'payment_currency_id' => $this->payment_currency_id,
            'is_split_payment' => $this->is_split_payment ?? false,
            'second_payment_currency_id' => $this->is_split_payment ? $this->second_payment_currency_id : null,
            'second_payment_amount' => $this->is_split_payment ? $this->second_payment_amount : null,
            'pay_at_prevailing_rate' => $this->pay_at_prevailing_rate ?? false,
        ];

        $response = $this->repository->createaward($data);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function update()
    {
        $data = [
            'purchaserequisition_id' => $this->purchaserequisition->id,
            'customer_id' => $this->customer_id,
            'tendernumber' => $this->tendernumber,
            'item' => $this->item,
            'quantity' => $this->quantity,
            'currency_id' => $this->currency_id,
            'amount' => $this->amount,
            'year' => $this->year,
            'payment_currency_id' => $this->payment_currency_id,
            'is_split_payment' => $this->is_split_payment ?? false,
            'second_payment_currency_id' => $this->is_split_payment ? $this->second_payment_currency_id : null,
            'second_payment_amount' => $this->is_split_payment ? $this->second_payment_amount : null,
            'pay_at_prevailing_rate' => $this->pay_at_prevailing_rate ?? false,
        ];
        $response = $this->repository->updateaward($this->id, $data);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->repository->deleteaward($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function edit($id)
    {
        $award = $this->repository->getaward($id);
        $this->customer_id = $award->customer_id;
        $this->customer = $award->customer;
        $this->tendernumber = $award->tendernumber;
        $this->item = $award->item;
        $this->quantity = $award->quantity;
        $this->amount = $award->amount;
        $this->currency_id = $award->currency_id;
        $this->payment_currency_id = $award->payment_currency_id ?? $award->currency_id;
        $this->is_split_payment = $award->is_split_payment ?? false;
        $this->second_payment_currency_id = $award->second_payment_currency_id;
        $this->second_payment_amount = $award->second_payment_amount;
        $this->pay_at_prevailing_rate = $award->pay_at_prevailing_rate ?? false;
        $this->id = $id;
        $this->awardmodal = true;
    }

    public function getpurchaseerequisition($id)
    {
        $this->purchaserequisition = $this->repository->getpurchaseerequisition($id);
        $this->modal = true;
    }

    public function savedocument()
    {
        $this->validate([
            'file' => 'required',
        ]);
        if ($this->purchaserequisitionawarddocument_id) {
            $this->updatedocument();
        } else {
            $this->createdocument();
        }
        $this->reset(['file', 'document', 'purchaserequisitionawarddocument_id']);
        $this->awarddocumentmodal = false;

    }

    public function createdocument()
    {
        $filepath = $this->file->store('awarddocuments', 'public');
        $data = [
            'purchaserequisitionaward_id' => $this->purchaserequisitionaward_id,
            'document' => $this->document,
            'filepath' => $filepath,
        ];
        $response = $this->repository->createawarddocument($data);
        if ($response['status'] == 'success') {
            $this->documents = $this->repository->getawarddocuments($this->purchaserequisitionaward_id);
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function ViewDocument($id)
    {
        $document = $this->documents->where('id', $id)->first();
        $this->currentdocument = asset('storage/'.$document->filepath);
        $this->viewdocumentmodal = true;
    }

    public function updatedocument()
    {
        $filepath = $this->file->store('awarddocuments', 'public');
        $data = [
            'purchaserequisitionaward_id' => $this->purchaserequisitionaward_id,
            'document' => $this->document,
            'filepath' => $filepath,
        ];
        $response = $this->repository->updateawarddocument($this->purchaserequisitionawarddocument_id, $data);
        if ($response['status'] == 'success') {
            $this->documents = $this->repository->getawarddocuments($this->purchaserequisitionaward_id);
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function deletedocument($id)
    {
        $response = $this->repository->deleteawarddocument($id);
        if ($response['status'] == 'success') {
            $this->documents = $this->repository->getawarddocuments($this->purchaserequisitionaward_id);
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function computequantitylimit()
    {
        $quantity = $this->purchaserequisition->quantity;
        $awarded = $this->purchaserequisition->awards()->sum('quantity');

        return $quantity - $awarded;
    }

    public function approve()
    {
        $response = $this->repository->approveaward($this->purchaserequisition->id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'year', 'label' => 'Year'],
            ['key' => 'prnumber', 'label' => 'PR Number'],
            ['key' => 'department.name', 'label' => 'Department'],
            ['key' => 'budgetitem', 'label' => 'Budget Item'],
            ['key' => 'purpose', 'label' => 'Purpose'],
            ['key' => 'quantity', 'label' => 'Quantity'],
            ['key' => 'unitprice', 'label' => 'Unit Price'],
            ['key' => 'total', 'label' => 'Total'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Created At'],
            ['key' => 'updated_at', 'label' => 'Updated At'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.awaitingpmu', [
            'rows' => $this->getawaitingpmu(),
            'headers' => $this->headers(),
            'documentlist' => $this->documentlist(),
            'currencies' => $this->currencies ?? collect(),
        ]);
    }
}
