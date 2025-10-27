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
    use WithPagination,Toast,WithFileUploads;
    public $search;
    public $year;
    protected $repository;
    protected $customerrepo;
    protected $currencyrepo;
    public $breadcrumbs;
    public $purchaserequisition =null;
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
    public bool $documentmodal = false;
    public $documents;
    public $purchaserequisitionaward_id;
    public  $purchaserequisitionawarddocument_id;
    public $file;
    public $document;
    public $currentdocument;
    public bool $awarddocumentmodal = false;
    public bool $viewdocumentmodal = false;
    public function mount()
    {
        $this->year = date("Y");
        $this->breadcrumbs = [
            ["label"=>"Home","link"=>route("admin.home")],
            ["label"=>"Procurement"],
        ];
        $this->documents = new Collection();
    }
    public function boot(ipurchaseerequisitionInterface $repository,icustomerInterface $customerrepo,icurrencyInterface $currencyrepo)
    {
        $this->repository = $repository;
        $this->customerrepo = $customerrepo;
        $this->currencyrepo = $currencyrepo;
    }

    public function documentlist():array{
        return [
            ['id'=>'Quotation','name'=>'Quotation'],
            ['id'=>'Evaluation Report','name'=>'Evaluation Report'],
            ['id'=>'Contract','name'=>'Contract'],
            ['id'=>'Purchase Order','name'=>'Purchase Order'],
        ];
    }
    public function getawaitingpmu()
    {
        return $this->repository->getpurchaseerequisitionbystatus($this->year,"AWAITING_PMU");
    }
    public function UpdatedRegnumber(){
        $customer = $this->customerrepo->getCustomerByRegnumber($this->regnumber);
        if($customer){
            $this->customer_id = $customer->id;
            $this->customer = $customer;
        }

    }

  public function getdocuments($id){
    $this->documents = $this->repository->getawarddocuments($id);
    $this->documentmodal = true;
    $this->purchaserequisitionaward_id = $id;
  }
    
    
    public function save(){
        $this->validate([
            'customer_id'=>'required',
            'tendernumber'=>'required',
            'item'=>'required',
            'quantity'=>'required|numeric',
            'amount'=>'required',
        ]);
        $maxquantity = $this->computequantitylimit();
        if($this->quantity > $maxquantity){
            $this->error("Quantity cannot exceed the Purchase Requisition Quantity");
            return;
        }
        if($this->id){
            $this->update();
        }else{
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
        ]);
        $this->awardmodal = false;
    }

    public function create(){
        $data = [
            'purchaserequisition_id'=>$this->purchaserequisition->id,
            'customer_id'=>$this->customer_id,
            'tendernumber'=>$this->tendernumber,
            'item'=>$this->item,
            'quantity'=>$this->quantity,
            'currency_id'=>$this->purchaserequisition->budgetitem->currency_id,
            'amount'=>$this->amount,
            'year'=>$this->year
        ];
     
        $response =$this->repository->createaward($data);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }
    public function update(){
        $data = [
            'purchaserequisition_id'=>$this->purchaserequisition->id,
            'customer_id'=>$this->customer_id,
            'tendernumber'=>$this->tendernumber,
            'item'=>$this->item,
            'quantity'=>$this->quantity,
            'currency_id'=>$this->currency_id,
            'amount'=>$this->amount,
            'year'=>$this->year
        ];
        $response =$this->repository->updateaward($this->id,$data);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function delete($id){
        $response =$this->repository->deleteaward($id);
        if($response["status"]=="success"){
            $this->success($response["message"]);
        }else{
            $this->error($response["message"]);
        }
    }

    public function edit($id){
        $award = $this->repository->getaward($id);
        $this->customer_id = $award->customer_id;
        $this->customer = $award->customer;
        $this->tendernumber = $award->tendernumber;
        $this->item = $award->item;
        $this->quantity = $award->quantity;
        $this->amount = $award->amount;
        $this->id = $id;
        $this->awardmodal = true;
    }

    public function getpurchaseerequisition($id)
    {
        $this->purchaserequisition = $this->repository->getpurchaseerequisition($id);
        $this->modal = true;
    }
    public function  savedocument(){
         $this->validate([
            "file"=>"required",
        ]);
        if($this->purchaserequisitionawarddocument_id){
            $this->updatedocument();
        }else{
            $this->createdocument();
        }
        $this->reset(['file','document','purchaserequisitionawarddocument_id']);
        $this->awarddocumentmodal = false;
         
    }

    public function createdocument(){
          $filepath = $this->file->store("awarddocuments","public");
          $data = [
            "purchaserequisitionaward_id"=>$this->purchaserequisitionaward_id,
            "document"=>$this->document,
            "filepath"=>$filepath
          ];
          $response = $this->repository->createawarddocument($data);
          if($response["status"]=="success"){
            $this->documents = $this->repository->getawarddocuments($this->purchaserequisitionaward_id);
            $this->success($response["message"]);
          }else{
            $this->error($response["message"]);
          }
    }
    public function ViewDocument($id){
        $document = $this->documents->where("id",$id)->first();
        $this->currentdocument = asset('storage/' . $document->filepath);
        $this->viewdocumentmodal = true;
    }

    public function updatedocument(){
          $filepath = $this->file->store("awarddocuments","public");
          $data = [
            "purchaserequisitionaward_id"=>$this->purchaserequisitionaward_id,
            "document"=>$this->document,
            "filepath"=>$filepath
          ];
          $response = $this->repository->updateawarddocument($this->purchaserequisitionawarddocument_id,$data);
          if($response["status"]=="success"){
            $this->documents = $this->repository->getawarddocuments($this->purchaserequisitionaward_id);
            $this->success($response["message"]);
          }else{
            $this->error($response["message"]);
          }
    }
    public function deletedocument($id){
          $response = $this->repository->deleteawarddocument($id);
          if($response["status"]=="success"){
            $this->documents = $this->repository->getawarddocuments($this->purchaserequisitionaward_id);
            $this->success($response["message"]);
          }else{
            $this->error($response["message"]);
          }
    }
        

    public function computequantitylimit(){
        $quantity = $this->purchaserequisition->quantity;
        $awarded = $this->purchaserequisition->awards()->sum("quantity");
        return $quantity - $awarded;
    }
    public function approve(){
        $response = $this->repository->approveaward($this->purchaserequisition->id);
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
            ["key"=>"department.name","label"=>"Department"],
            ["key"=>"budgetitem","label"=>"Budget Item"],            
            ["key"=>"purpose","label"=>"Purpose"],
            ["key"=>"quantity","label"=>"Quantity"],
            ["key"=>"unitprice","label"=>"Unit Price"],
            ["key"=>"total","label"=>"Total"],
            ["key"=>"status","label"=>"Status"],
            ["key"=>"created_at","label"=>"Created At"],
            ["key"=>"updated_at","label"=>"Updated At"],
            ["key"=>"action","label"=>""]
        ];
    }
    public function render()
    {
        return view('livewire.admin.workflows.awaitingpmu',[
            "rows"=>$this->getawaitingpmu(),
            "headers"=>$this->headers(),
            "documentlist"=>$this->documentlist(),
            
        ]);
    }
}
