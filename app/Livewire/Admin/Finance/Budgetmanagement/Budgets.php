<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\icurrencyInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Budgets extends Component
{
    use Toast;
    protected $repo;
    protected $currencyrepo;
    public $year;
    public $currency;
    public $status;
    public $id;
    public $modal = false;
    public $breadcrumbs = [];
    public function mount()
    {
        $this->breadcrumbs = [
            [
                "label" => "Dashboard",
                "link" => route("admin.home"),
            ],
            [
                "label" => "Budget Management"
            ],
        ];
    }
    public function boot(ibudgetInterface $repo, icurrencyInterface $currencyrepo)
    {
        $this->repo = $repo;
        $this->currencyrepo = $currencyrepo;
    }

    public function getdata()
    {
        return $this->repo->getbudgets();
    }
    public function getcurrencies(){
        return $this->currencyrepo->getcurrencies();
    }
    public function headers():array{
        return [
            ["key"=>"year", "label"=>"Year"],
            ["key"=>"currency", "label"=>"Currency"],
            ["key"=>"createdby", "label"=>"Created By"],
            ["key"=>"updatedby", "label"=>"Updated By"],
            ["key"=>"approvedby", "label"=>"Approved By"],
            ["key"=>"status", "label"=>"Status"],
            ["key"=>"action", "label"=>""]
        ];
    }
    public function  edit($id){
        $this->id = $id;
         $record =$this->repo->getbudget($id);
         $this->year = $record->year;
         $this->currency = $record->currency_id;
         $this->modal = true;
    }
    
    public function save(){
        $this->validate([
            "year"=>"required",
            "currency"=>"required"
        ]);
        if($this->id){
           $this->update();
        }else{
            $this->create();
        }
        $this->reset([
            "year",
            "currency",
            "id"
            ]);
    }
    public function create(){
        $response = $this->repo->createbudget(["year"=>$this->year,"currency_id"=>$this->currency]);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

  

    public function update(){
        $response = $this->repo->updatebudget($this->id, ["year"=>$this->year,"currency_id"=>$this->currency]);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function delete($id){
        $response = $this->repo->deletebudget($id);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function approve($id){
        $response = $this->repo->approvebudget($id);
        if($response["status"] == "success"){
           $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.budgets',['data'=>$this->getdata(),'headers'=>$this->headers(),'currencies'=>$this->getcurrencies()]);
    }
}
