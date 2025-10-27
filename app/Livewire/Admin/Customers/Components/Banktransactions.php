<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\ibanktransactionInterface;
use App\Interfaces\repositories\icustomerInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Banktransactions extends Component
{
    use Toast;
    public $customer_id;
    public $breadcrumbs =[];
    public $search;
    public bool $modal = false;
    public $transactions;
    public $customer;
    protected $banktransactionrepo;
    protected $customerrepo;

    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->transactions = new Collection();
        $this->getcustomer();
        $this->breadcrumbs = [
            ["label" => "Customers", "link" => route("admin.customers.showlist")],
            ["label" => "customer", "link" => route("admin.customers.show", $this->customer_id)],
            ["label" => "Bank Transactions"],
        ];
    }
    public function boot(ibanktransactionInterface $banktransactionrepo, icustomerInterface $customerrepo){
        $this->banktransactionrepo = $banktransactionrepo;
        $this->customerrepo = $customerrepo;
    }

    public function getbanktransactions(){
        return $this->banktransactionrepo->gettransactions($this->customer_id);
    }
    public function UpdatedSearch(){
       $this->searchtransactions();
    }
    public function getcustomer(){
        $this->customer = $this->customerrepo->getCustomerById($this->customer_id);
    }
    public function searchtransactions(){
        if($this->search==""){
        
            return;
        }
        $this->transactions = $this->banktransactionrepo->internalsearch($this->search);
    }

    public function claim($id){
        $transaction = $this->transactions->where("id",$id)->first();
        $response = $this->banktransactionrepo->claim([
            "sourcereference"=>$transaction->sourcereference,
            "regnumber"=>$this->customer->regnumber
        ]);
        if($response['status']=="ERROR"){
            $this->error($response['message']);
            return;
        }
        $this->success($response['message']);
        $this->modal = false;
    }

    public function headers():array{
        return [
            ["key"=>"sourcereference","label"=>"Source Reference"],
            ["key"=>"accountnumber","label"=>"Account Number"],
            ["key"=>"description","label"=>"Description"],
            ["key"=>"transactiondate","label"=>"Transaction Date"],
            ["key"=>"amount","label"=>"Amount"],
            ["key"=>"status","label"=>"Status"]
        ];
    }
    public function render()
    {
        return view('livewire.admin.customers.components.banktransactions',[
            'banktransactions'=>$this->getbanktransactions(),
            'headers'=>$this->headers()
        ]);
    }
}
