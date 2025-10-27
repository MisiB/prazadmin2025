<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\ibanktransactionInterface;
use Livewire\Component;

class Bankreconciliationreport extends Component
{
    public $bankreconciliation = null;
    public $id;
    public $breadcrumbs = [];
    public array $expanded = [2];
    public bool $showdebit = false;
    public string $filterbystatus = "ALL";
    public string $selectedTab = "data-tab";
    protected $banktransactionrepo;

    public function boot(ibanktransactionInterface $banktransaction){
        $this->banktransactionrepo = $banktransaction;
    }
    public function mount($id){
        $this->id = $id;
        $this->breadcrumbs = [
            ["label"=>"Home","link"=>route("admin.home")],
            ["label"=>"Bank Transactions","link"=>route("admin.finance.banktransactions")],
            ["label"=>"Bank Reconciliation Report"],
        ];
       
    }
    public function headers():array{
        return [
            ["key"=>"tnxdate","label"=>"Date"],
            ["key"=>"tnxreference","label"=>"Reference"],

            ["key"=>"tnxtype","label"=>"Type"],
            ["key"=>"tnxamount","label"=>"Amount"],
            ["key"=>"claimed","label"=>"Claimed By"],
            ["key"=>"utilization","label"=>"Utilization"],
            ["key"=>"walletbalance","label"=>"Wallet Balance"],            
            ["key"=>"status","label"=>"Status"],
            ["key"=>"action","label"=>""],
        ];
    }
  
    public function getreport(){
        $this->bankreconciliation = $this->banktransactionrepo->viewreport($this->id,$this->filterbystatus,$this->showdebit);
    }
    public function render()
    {
        $this->getreport();
        return view('livewire.admin.finance.reports.bankreconciliationreport',['bankreconciliation'=>$this->bankreconciliation,'headers'=>$this->headers()]);
    }
}
