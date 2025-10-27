<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Awaitingdelivary extends Component
{
    use WithPagination,Toast;
    public $search;
    public $year;
    public $breadcrumbs=[];
    protected $repository;
    public $purchaserequisition =null;
    public $modal = false;
    public $documents;
    public $documentmodal = false;
    public $purchaserequisitionaward_id = null;
    public $currentdocument = null;
    public $viewdocumentmodal = false;
    public function mount()
    {
        $this->year = date("Y");
        $this->documents = new Collection();
        $this->breadcrumbs = [
            ["label" => "Home", "link" => route("admin.home")],
            ["label" => "Awaiting Delivery"],
        ];
    }
    public function boot(ipurchaseerequisitionInterface $repository)
    {
        $this->repository = $repository;
    }
    public function getawaitingdelivary()
    {
        return $this->repository->getpurchaseerequisitionbystatus($this->year,"AWAITING_DELIVERY");
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

    public function getpurchaseerequisition($id)
    {
        $this->purchaserequisition = $this->repository->getpurchaseerequisition($id);
        $this->modal = true;
    }
    public function ViewDocument($id){
        $document = $this->documents->where("id",$id)->first();
        $this->currentdocument = asset('storage/' . $document->filepath);
        $this->viewdocumentmodal = true;
    }
    public function getdocuments($id){
        $this->documents = $this->repository->getawarddocuments($id);
        $this->documentmodal = true;
        $this->purchaserequisitionaward_id = $id;
      }
    public function render()
    {
        return view('livewire.admin.workflows.awaitingdelivary',[
            "rows"=>$this->getawaitingdelivary(),
            "headers"=>$this->headers()
        ]);
    }
}
