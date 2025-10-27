<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use App\Interfaces\repositories\iworkflowInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class Purchaserequisitionlist extends Component
{
    public $breadcrumbs =[];
    public $year;
    public $selectedTab;
    protected $repository;
    public $selectedpurchaserequisitions;
    public bool $modal = false;
    protected $workflowRepository;
    public function mount(){
        $this->year = date("Y");
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Purchase Requisition approvals']
        ];
        
       $this->selectedpurchaserequisitions = new Collection();
    }

    public function boot(ipurchaseerequisitionInterface $repository,iworkflowInterface $workflowRepository){
        $this->repository = $repository;
        $this->workflowRepository = $workflowRepository;
    }
    public function getpurchaserequisitionlist(){
        return $this->repository->getpurchaseerequisitions($this->year);
    }
    public function getworkflowbystatus(){
        $name = config("workflow.purchase_requisitions");
        return $this->workflowRepository->getworkflowbystatus($name);
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
            ["key"=>"action","label"=>""]
        ];
    }
    public function setdata($status){
        $this->selectedpurchaserequisitions = $this->getpurchaserequisitionlist()->where('status', $status);
        $this->modal = true;
    }
    public function render()
    {
        return view('livewire.admin.workflows.approvals.purchaserequisitionlist',[
            "purchaserequisitions"=>$this->getpurchaserequisitionlist(),
            "headers"=>$this->headers(),
            "workflow"=>$this->getworkflowbystatus()
        ]);
    }
}
