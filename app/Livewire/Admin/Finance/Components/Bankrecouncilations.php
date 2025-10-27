<?php

namespace App\Livewire\Admin\Finance\Components;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;

class Bankrecouncilations extends Component
{
    use Toast,WithFileUploads;
    public $year;
    public $startdate;
    public $enddate;
    public $closingbalance;
    public $openingbalance;
    public $bankaccount;
    public $currency;
    public $file;
    public $status;
    public $id;
    public $modal = false;
    public $viewmodal = false;
    public $bankreconciliation = null;
    protected $bankaccountrepo;
    protected $banktransactionrepo;

    public function boot(ibankaccountInterface $bankaccount, ibanktransactionInterface $banktransaction){
        $this->bankaccountrepo = $bankaccount;
        $this->banktransactionrepo = $banktransaction;
    }

    public function mount(){
        $this->year = date("Y");
    }
    public function getbankaccounts(){
        return $this->bankaccountrepo->getBankAccounts();
    }

    public function getbankreconciliations(){
        return $this->banktransactionrepo->getbankreconciliations($this->year);
    }
    public function view($id){
        $this->bankreconciliation = $this->banktransactionrepo->getbankreconciliation($id);
        $this->viewmodal = true;
       
    }
    public function edit($id){
        $this->id = $id;
        $bankreconciliation = $this->banktransactionrepo->getbankreconciliation($id);
        $this->year = $bankreconciliation->year;
        $this->startdate = $bankreconciliation->start_date;
        $this->enddate = $bankreconciliation->end_date;
        $this->closingbalance = $bankreconciliation->closing_balance;
        $this->openingbalance = $bankreconciliation->opening_balance;
        $this->bankaccount = $bankreconciliation->bankaccount_id;
        $this->currency = $bankreconciliation->currency_id;
        $this->status = $bankreconciliation->status;
        $this->modal = true;
    }
    public function save(){
        $this->validate([
            "year"=>"required",
            "startdate"=>"required",
            "enddate"=>"required",
            "closingbalance"=>"required",
            "openingbalance"=>"required",
            "bankaccount"=>"required",
        ]);
        $filename = null;
        if($this->file!=null){
        
            
            $filename = $this->file->store("bankreconciliations");
        }

        if($this->id==null){
            $this->validate([
                "file"=>"required",
            ]);
            $this->create($filename);
        }else{
            $this->update($filename);
        }
        $this->reset([
            "year",
            "startdate",
            "enddate",
            "closingbalance",
            "openingbalance",
            "bankaccount",
            "currency",
            "file",
            "status",
            "id"
            ]);
    }
    public function create($filename){
     $response = $this->banktransactionrepo->createbankreconciliation([
        "year" => $this->year,
        "start_date" => $this->startdate,
        "bankaccount_id" => $this->bankaccount,
        "currency_id" => $this->getbankaccounts()->where("id","=",$this->bankaccount)->first()->currency_id,
        "end_date" => $this->enddate,
        "closing_balance" => $this->closingbalance,
        "opening_balance" => $this->openingbalance,
        "filename" => $filename,
        "user_id" => Auth::user()->id,
     ]);
     if($response['status']=='SUCCESS'){
      
        $this->success($response['message']);
     }else{
        $this->error($response['message']);
     }
    }
    public function update($filename){
     $response = $this->banktransactionrepo->updatebankreconciliation($this->id,[
        "year" => $this->year,
        "start_date" => $this->startdate,
        "end_date" => $this->enddate,
        "closing_balance" => $this->closingbalance,
        "opening_balance" => $this->openingbalance,
        "filename" => $filename,
        "user_id" => Auth::user()->id,
        "status" => $this->status,
     ]);
     if($response['status']=='SUCCESS'){
      
        $this->success($response['message']);
     }else{
        $this->error($response['message']);
     }

    }

    public function delete($id){
     $response = $this->banktransactionrepo->deletebankreconciliation($id);
     if($response['status']=='SUCCESS'){
      
         $this->success($response['message']);
     }else{
         $this->error($response['message']);
     }
    }
    public function extractdata($id){
        $response = $this->banktransactionrepo->extractdata($id);
        if($response['status']=='SUCCESS'){
         
             $this->success($response['message']);
         }else{
             $this->error($response['message']);
         }
    }
    public function syncdata($id){
        $response = $this->banktransactionrepo->syncdata($id);
        if($response['status']=='SUCCESS'){
         
             $this->success($response['message']);
         }else{
             $this->error($response['message']);
         }
    }

    public function viewreport($id){
        $this->bankreconciliation = $this->banktransactionrepo->viewreport($id);
        $this->viewmodal = true;
       
    }

    public function headers():array{
        return [
            ["key"=>"year","label"=>"Year"],
            ["key"=>"bankaccount.account_number","label"=>"Bank Account"],
            ["key"=>"dates","label"=>"Dates"],
            ["key"=>"balances","label"=>"Balances"],
            ["key"=>"status","label"=>"Status"],
            ["key"=>"user.name","label"=>"Created By"],
            ["key"=>"action","label"=>""],
        ];
    }
    
    public function render()
    {
        return view('livewire.admin.finance.components.bankrecouncilations',[
            "headers"=>$this->headers(),
            "bankreconciliations"=>$this->getbankreconciliations(),
            "bankaccounts"=>$this->getbankaccounts(),
        ]);
    }
  
}
