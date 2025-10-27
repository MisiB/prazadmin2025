<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\iepaymentInterface;
use Livewire\Component;

class Epayments extends Component
{
    public $customer_id;
    protected $repo;
    public $breadcrumbs =[];
    public  function mount($customer_id){
        $this->customer_id = $customer_id;
        $this->breadcrumbs = [
            ["label" => "Customers", "link" => route("admin.customers.showlist")],
            ["label" => "Customer", "link" => route("admin.customers.show", $this->customer_id)],
            ["label" => "ePayments"],
        ];
    }
    public function boot(iepaymentInterface $repo){
        $this->repo = $repo;
    }

    public function getepayments(){
        return $this->repo->getepayments($this->customer_id);
    }

    public function headers():array{
        return [
            ["key"=>"created_at","label"=>"Date"],
            ["key"=>"invoice.invoicenumber","label"=>"Invoice Number"],
            ["key"=>"transactiondate","label"=>"Transaction Date"],
            ["key"=>"amount","label"=>"Amount"],
            ["key"=>"source","label"=>"Source"],
            ["key"=>"status","label"=>"Status"],
        
    ];
    }
    public function render()
    {
        return view('livewire.admin.customers.components.epayments',[
            'epayments' => $this->getepayments(),
            'headers' => $this->headers()
        ]);
    }
}
