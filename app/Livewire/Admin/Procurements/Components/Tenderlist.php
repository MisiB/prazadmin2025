<?php

namespace App\Livewire\Admin\Procurements\Components;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\itenderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Mary\Traits\Toast;

class Tenderlist extends Component
{
    use Toast;
    public $search;
    protected $tenderrepo;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    public $tender=null;
    public $modal = false;
    public $edittendermodal = false;
    public $tender_number;
    public $tender_title;
    public $status;
    public $tender_type;
    public $closing_date;
    public $closing_time;
    public $tender_description;
    public $addtenderfeemodal = false;
    public $inventoryitem_id;
    public $currency_id;
    public $amount;
    public $validityperiod;
    public $tender_id;
    public $tenderfee_id;
    public function boot(itenderInterface $tenderrepo,iinventoryitemInterface $inventoryitemrepo,icurrencyInterface $currencyrepo)
    {
        $this->tenderrepo = $tenderrepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->currencyrepo = $currencyrepo;
    }

    public function gettenders():LengthAwarePaginator
    {
        return $this->tenderrepo->gettenders($this->search);
    }

    public function getinventoryitems()
    {
        return $this->inventoryitemrepo->getinventories();
    }
    public function getcurrencies()
    {
        return $this->currencyrepo->getcurrencies();
    }

    public function headers():array{
        return [
            ['key'=>'tender','label'=>'Tender'],
            ['key'=>'dates','label'=>'Dates'],
            ['key'=>'status','label'=>'Status'],
            ['key'=>'action','label'=>''],
        ];
    }

    public function statuslist():array{
        return [
            ["id"=>"PUBLISHED","name"=>"Published"],
            ["id"=>"CANCELLED","name"=>"Cancelled"]
        ];
    }
    public function gettendertypes()
    {
        return $this->tenderrepo->gettendertypes();
    }

    public function gettender($id){
        $this->tender_id = $id;
        $this->tender= $this->tenderrepo->gettender($id);   
        $this->modal = true;
    }

    public function edittender(){
        $this->edittendermodal = true;
        $this->tender_number = $this->tender->tender_number;
        $this->tender_title = $this->tender->tender_title;
        $this->status = $this->tender->status;
        $this->tender_type = $this->tender->tendertype_id;
        $this->closing_date = $this->tender->closing_date;
        $this->closing_time = $this->tender->closing_time;
        $this->tender_description = $this->tender->tender_description;
    }

    public function savetender(){
       $response= $this->tenderrepo->updatetender($this->tender->id,["tender_number"=>$this->tender_number,"tender_title"=>$this->tender_title,"status"=>$this->status,"tendertype_id"=>$this->tender_type,"closing_date"=>$this->closing_date,"closing_time"=>$this->closing_time,"tender_description"=>$this->tender_description,"suppliercategories"=>[]]);
       if($response['status']=='success'){

        $this->success($response['message']);
       }else{
        $this->error($response['message']);
       }
       $this->edittendermodal = false;
    }

    public function getfee($id){
        $this->tenderfee_id = $id;
        $fee = $this->tenderrepo->gettenderfee($id);
        $this->tender_id = $fee->tender_id;
        $this->inventoryitem_id = $fee->inventoryitem_id;
        $this->currency_id = $fee->currency_id;
        $this->amount = $fee->amount;
        $this->validityperiod = $fee->validityperiod;
        $this->addtenderfeemodal = true;
    }
    public function savetenderfee(){
     if($this->tenderfee_id){
        $this->updatefee();
     }else{
        $this->createfee();
     }
     $this->reset(['tenderfee_id','inventoryitem_id','currency_id','amount','validityperiod']);
    }

    public function createfee(){
        $response= $this->tenderrepo->createtenderfee([
            "tender_id" => $this->tender_id,
            "inventoryitem_id" => $this->inventoryitem_id,
            "currency_id" => $this->currency_id,
            "amount" => $this->amount,
            "validityperiod" => $this->validityperiod,
        ]);
        if($response['status']=='success'){

            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
        $this->addtenderfeemodal = false;
    }

    public function updatefee(){
        $response= $this->tenderrepo->updatetenderfee($this->tenderfee_id,["tender_id"=>$this->tender_id,"inventoryitem_id"=>$this->inventoryitem_id,"currency_id"=>$this->currency_id,"amount"=>$this->amount,"validityperiod"=>$this->validityperiod]);
        if($response['status']=='success'){

            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
        $this->addtenderfeemodal = false;
    }
    public function deletefee($id){
        $response= $this->tenderrepo->deletetenderfee($id);
        if($response['status']=='success'){

            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
        $this->addtenderfeemodal = false;
    }
    public function render()
    {
        return view('livewire.admin.procurements.components.tenderlist',[
            'tenders'=>$this->gettenders(),
            'headers'=>$this->headers(),
            'statuses'=>$this->statuslist(),
            'tendertypes'=>$this->gettendertypes(),
            'inventoryitems'=>$this->getinventoryitems(),
            'currencies'=>$this->getcurrencies(),
            ]);
    }
}
