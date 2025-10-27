<?php

namespace App\Livewire\Admin\Procurements\Components;

use App\Interfaces\repositories\icurrencyInterface;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Interfaces\repositories\itenderInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\iinventoryitemInterface;

class Createtender extends Component
{
    use Toast;
    protected $tenderrepo;
    protected $customerrepo;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    public $tender_number;
    public $tender_title;
    public $tender_description;
    public $tender_type;
    public $customer_id;
    public $closing_date;
    public  $closing_time;
    public $status;
    public $search;
    public $modal;
    public $tenderfees =[];
    public $inventoryitem_id;
    public $currency_id;
    public $amount;
    public $validityperiod=0;
    

    public function boot(itenderInterface $tenderrepo,icurrencyInterface $currencyrepo, icustomerInterface $customerrepo,iinventoryitemInterface $inventoryitemrepo)
    {
        $this->tenderrepo = $tenderrepo;
        $this->customerrepo = $customerrepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->currencyrepo = $currencyrepo;
    }
   public function getcustomers()
    {
        if($this->search){
            return $this->customerrepo->search($this->search);
        }
        return [];
    }
    public function selectCustomer($id)
    {
        $this->customer_id = $id;
        $this->modal = true;
    }
    public function getinventoryitems()
    {
        return $this->inventoryitemrepo->getinventories();
    }
    public function gettendertypes()
    {
        return $this->tenderrepo->gettendertypes();
    }
    public function getcurrencies()
    {
        return $this->currencyrepo->getcurrencies();
    }
    public function headers():array{
        return [
            ["key"=>"name","label"=>"Name"],
            ["key"=>"regnumber","label"=>"Regnumber"],
            ["key"=>"type","label"=>"Type"],
            ["key"=>"country","label"=>"Country"],            
            ["key"=>"action","label"=>"Action"],
        ];
    }
    public function statuslist():array{
        return [
            ["id"=>"PUBLISHED","name"=>"Published"],
            ["id"=>"CANCELLED","name"=>"Cancelled"]
        ];
    }

    public function save(){
        $this->validate([
            "tender_number"=>"required",
            "tender_title"=>"required",
            "tender_type"=>"required",
            "customer_id"=>"required",
            "closing_date"=>"required",
            "closing_time"=>"required",
            "status"=>"required",
            "tender_description"=>"required"
        ]);
        $data = [
            "tender_number"=>$this->tender_number,
            "tender_title"=>$this->tender_title,
            "tendertype_id"=>$this->tender_type,
            "customer_id"=>$this->customer_id,
            "closing_date"=>$this->closing_date,
            "closing_time"=>$this->closing_time,
            "source"=>"MANUAL",
            'tender_id'=>null,
            "status"=>$this->status,
            "tender_description"=>$this->tender_description,
            "suppliercategories"=>[],
            "tenderfees"=>$this->tenderfees
        ];
       $response =  $this->tenderrepo->create($data);
       if($response['status']=='success'){
    
        $this->success($response['message']);
       }else{
        $this->error($response['message']);
       }
       $this->reset([
        "tender_number",
        "tender_title",
        "tender_type",
        "customer_id",
        "closing_date",
        "closing_time",
        "status",
        "tender_description"
       ]);
    }

    public function addtenderfee(){
        $this->tenderfees[] = [
            "inventoryitem_id"=>$this->inventoryitem_id,
            "currency_id"=>$this->currency_id,
            "amount"=>$this->amount,
            "validityperiod"=>$this->validityperiod
        ];
        $this->inventoryitem_id = null;
        $this->currency_id = null;
        $this->amount = null;
        $this->validityperiod = null;
    }

    public function removetenderfee($index){
        unset($this->tenderfees[$index]);
    }
    public function render()
    {
        return view('livewire.admin.procurements.components.createtender',[
            "customers"=>$this->getcustomers(),
            "headers"=>$this->headers(),
            "tendertypes"=>$this->gettendertypes(),
            "statuses"=>$this->statuslist(),
            "currencies"=>$this->getcurrencies(),
            "inventoryitems"=>$this->getinventoryitems()
        ]);
    }
}
