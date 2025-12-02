<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\isuspenseInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ReversedTransactions extends Component
{
    use WithPagination;
    
    public $customer_id;
    protected $suspenserepo;
    protected $customerrepo;
    public $breadcrumbs = [];
    public $customer;

    public function boot(isuspenseInterface $suspenserepo, icustomerInterface $customerrepo)
    {
        $this->suspenserepo = $suspenserepo;
        $this->customerrepo = $customerrepo;
    }

    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->customer = $this->customerrepo->getCustomerById($customer_id);
        $this->breadcrumbs = [
            ["link" => route("admin.customers.showlist"), "label" => "Customers"],
            ["link" => route("admin.customers.show", $this->customer_id), "label" => "Customer"],
            ["label" => "Reversed Transactions"],
        ];
    }

    public function getReversedTransactions(): LengthAwarePaginator
    {
        return $this->suspenserepo->getReversedTransactionsPaginated($this->customer_id, 15);
    }

    public function headers(): array
    {
        return [
            ["key" => "invoice_number", "label" => "Invoice Number"],
            ["key" => "receipt_number", "label" => "Receipt Number"],
            ["key" => "amount", "label" => "Amount"],
            ["key" => "reversed_at", "label" => "Reversed At"],
            ["key" => "reversed_by", "label" => "Reversed By"],
        ];
    }

    public function render()
    {
        return view('livewire.admin.customers.components.reversed-transactions', [
            "reversedTransactions" => $this->getReversedTransactions(),
            "headers" => $this->headers(),
        ]);
    }
}
