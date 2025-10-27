<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Purchaserequisitions extends Component
{
    use Toast;
    public $breadcrumbs =[];
    public $search;
    public $year;
    public $modal;
    public $id;
    public $purpose;
    public $quantity;
    public $description;
    public $budgetitem_id;
    public $unitprice;
    public $total;
    public $budget_id;
    public $maxbudget=0;
    public $maxquantity=0;
    public $purchaserequisition;
    
    protected $purchaserequisitionrepo;
    protected $budgetrepo;

    public function boot(ipurchaseerequisitionInterface $purchaserequisitionrepo,ibudgetInterface $budgetrepo){
        $this->purchaserequisitionrepo = $purchaserequisitionrepo;
        $this->budgetrepo = $budgetrepo;
    }
    public function mount(){
        $this->breadcrumbs = [            
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Purchase Requisitions']
        ];
        $this->year = date("Y");
        $this->quantity = 1;
        $this->getbudgets();
        $this->getbudgetitems();
    }
    public function getbudgets(){
        $budgets = $this->budgetrepo->getbudgets();
        $budget = $budgets->where("year",$this->year)->first();
        $this->budget_id = $budget->id;
        
    }
    public function getbudgetitems(){
        return $this->budgetrepo->getbudgetitemsbydepartment($this->budget_id,Auth::user()->department->department_id);
    }
    public function getpurchaserequisitions(){
        return $this->purchaserequisitionrepo->getpurchaseerequisitionbydepartment($this->year,Auth::user()->department->department_id);
    }
    public function edit($id){
        $this->id = $id;
        $this->purchaserequisition = $this->purchaserequisitionrepo->getpurchaseerequisition($id);
        $this->budgetitem_id = $this->purchaserequisition->budgetitem_id;
        $this->quantity = $this->purchaserequisition->quantity;
        $this->purpose = $this->purchaserequisition->purpose;
        $this->description = $this->purchaserequisition->description;
        $this->refreshdata($this->budgetitem_id);
        $this->modal = true;
    }
    public function refreshdata($id){
        $budgetitem = $this->getbudgetitems()->where("id",$id)->first();
        $this->unitprice = $budgetitem->unitprice;
       
        $totalquantity = $budgetitem->purchaserequisitions->sum("quantity");
        $totalprocured = $totalquantity * $budgetitem->unitprice;
        $outgoingvirements = $budgetitem->outgoingvirements->sum("amount");
        $incomingvirements = $budgetitem->incomingvirements->sum("amount");
        $this->maxbudget = $budgetitem->total - $totalprocured - $outgoingvirements + $incomingvirements;
        $this->maxquantity = $budgetitem->quantity - $totalquantity;    
        $this->total = $budgetitem->unitprice * $this->quantity;
    }

    public function UpdatedBudgetitemid($value){
        $budgetitem = $this->getbudgetitems()->where("id",$value)->first();
        $this->unitprice = $budgetitem->unitprice;
       
        $totalquantity = $budgetitem->purchaserequisitions->sum("quantity");
        $totalprocured = $totalquantity * $budgetitem->unitprice;
        $outgoingvirements = $budgetitem->outgoingvirements->sum("amount");
        $incomingvirements = $budgetitem->incomingvirements->sum("amount");
        $this->maxbudget = $budgetitem->total - $totalprocured - $outgoingvirements + $incomingvirements;
        $this->maxquantity = $budgetitem->quantity - $totalquantity;    
        $this->total = $budgetitem->unitprice * $this->quantity;
    }
    public function UpdatedQuantity($value){
        $this->total = (int)$this->unitprice * $value;
    }
    public function save(){
 $this->validate([
    "budgetitem_id"=>"required",
    "quantity"=>"required",
    "unitprice"=>"required",
    "total"=>"required",
    "purpose"=>"required",
    "description"=>"required"
 ]);
 if($this->id !=null){
    $this->update();
 }else{
    $this->create();
 }
 $this->reset(['budgetitem_id','quantity','purpose','description']);

    }

    public function create(){
        $response=$this->purchaserequisitionrepo->createpurchaseerequisition([
            "budgetitem_id"=>$this->budgetitem_id,
            "quantity"=>$this->quantity,
            "purpose"=>$this->purpose,
            "description"=>$this->description
        ]);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function update(){
        $response=$this->purchaserequisitionrepo->updatepurchaseerequisition($this->id, [
            "budgetitem_id"=>$this->budgetitem_id,
            "quantity"=>$this->quantity,
            "purpose"=>$this->purpose,
            "description"=>$this->description
        ]);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function delete($id){
        $response=$this->purchaserequisitionrepo->deletepurchaseerequisition($id);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function headers():array{
        return [
            ["key"=>"year","label"=>"Year"],
            ["key"=>"prnumber","label"=>"PR Number"],
            ["key"=>"budgetitem","label"=>"Budget Item"],            
            ["key"=>"purpose","label"=>"Purpose"],
            ["key"=>"quantity","label"=>"Quantity"],
            ["key"=>"unitprice","label"=>"Unit Price"],
            ["key"=>"total","label"=>"Total"],
            ["key"=>"status","label"=>"Status"],
            ["key"=>"action","label"=>""]
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.purchaserequisitions',[
            "breadcrumbs"=>$this->breadcrumbs,
            "purchaserequisitions"=>$this->getpurchaserequisitions(),
            "budgetitems"=>$this->getbudgetitems(),
            "headers"=>$this->headers()
        ]);
    }
} 
