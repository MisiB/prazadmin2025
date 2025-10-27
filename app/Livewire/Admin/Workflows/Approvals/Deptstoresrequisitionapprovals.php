<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\ihodstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iissuerstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\ireceiverstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\istoresrequisitionService;
use App\Notifications\StoresrequisitionapprovalNotify;
use App\Notifications\StoresrequisitionapprovalSubmitted;
use App\Notifications\StoresrequisitiondeliveryNotification;
use App\Notifications\StoresrequisitionopeningNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Str;


class Deptstoresrequisitionapprovals extends Component
{


    Use Toast;
    protected $storesrequisitionService,$user;
    public $storestabs="approval-tab";
    public $statuslist =[];
    public $addrequisitionmodal=false, $requisitionverificationmodal=false, $approvalrequisitionmodal=false, $viewrequisitionmodal=false;
    public $itemdetail, $requiredquantity, $purposeofrequisition;
    public $hodid, $departmentid=0;
    public $issuerquantity, $adminvalidatorcomment;
    public $deliveryrequisitionuuid, $deliveryinitiatorid;
    public $itemfields = [], $viewfields=[], $deliveryfields=[]; // Number of items in the requisition form
    public $isapproved=false;
    public $statussearch;
    public $searchuuid;
    public $storesrequisitionsawaitingdelivery=[];

    public function boot(istoresrequisitionService $storesrequisitionService)
    {
        $this->storesrequisitionService=$storesrequisitionService;
        $this->user=Auth::user();
    }

    public function mount()
    {
        $this->itemfields[] = [ 'itemdetail' => '','requiredquantity' => ''];
        $this->hodid=$this->user->department->reportto;
        $this->statuslist=[ ['id'=>'A', 'name'=>'Approved'], ['id'=>'O', 'name'=>'Opened'], ['id'=>'V', 'name'=>'Awaiting Clearance'] ];      
        try
        {
            $this->departmentid = $this->storesrequisitionService->getuserdepartmentid($this->user->email);
        }catch (\Exception $e)
        {
            return $this->toast('error', $e->getMessage());
        }  
        $this->storesrequisitionsawaitingdelivery=$this->getstoresrequisitionsawaitingdelivery();
    
    }

    public function updated(){$this->storesrequisitionsawaitingdelivery=$this->getstoresrequisitionsawaitingdelivery();}

    public function headersforpendingrequisitions(): array
    { 
        return [
            ['label' => 'Item Banner', 'key' => 'itembanner'],
            ['label' => 'Purpose of requisition', 'key' => 'purposeofrequisition'],
            ['label' => 'Item classes required', 'key' => 'itemscount'],
            ['label' => 'Initiator', 'key' => 'initiator'],
            ['label' => 'Status', 'key' => 'status'],
            ['label'=>'Actions', 'key' => 'actions']
        ];
    }
    public function headersforapprovedrequisitions(): array
    { 
        return [
            ['label' => 'Item Banner', 'key' => 'itembanner'],
            ['label' => 'Purpose of requisition', 'key' => 'purposeofrequisition'],
            ['label' => 'Item classes required', 'key' => 'itemscount'],
            ['label' => 'Receiver', 'key' => 'initiator'],
            ['label' => 'Status', 'key' => 'status'],
            ['label'=>'Actions', 'key' => 'actions']
        ];
    } 

    // Get stores requisitions awaiting delivery implemented on tabs
    public function getstoresrequisitionsawaitingdelivery()
    {
        return $this->storesrequisitionService->getstoresrequisitionsawaitingdelivery($this->departmentid, $this->searchuuid, $this->statussearch);
    }
    // Get stores requisitions awaiting issuing implemented on count cards
    public function getawaitingissuingstoresrequisitions()
    {
        return $this->storesrequisitionService->getawaitingissuingstoresrequisitions($this->departmentid, $this->searchuuid);
    }    
    public function getstoresrequisitionsawaitingapproval()
    {
        return $this->storesrequisitionService->getstoresrequisitionsawaitingapproval($this->departmentid, $this->searchuuid);
    }
    public function getstoresrequisitionsawaitingclearance()
    {
        return $this->storesrequisitionService->getstoresrequisitionsawaitingclearance($this->departmentid, $this->searchuuid);
    }
    public function getdeliveredstoresrequisitions()
    {
        return $this->storesrequisitionService->getdeliveredstoresrequisitions($this->departmentid, $this->searchuuid);
    }

    public function getrecievedstoresrequisitions()
    {
        return $this->storesrequisitionService->getrecievedstoresrequisitions($this->departmentid, $this->searchuuid);
    }
    public function getrejectedstoresrequisitions()
    {
        return $this->storesrequisitionService->getrejectedstoresrequisitions($this->departmentid, $this->searchuuid);
    }

    public function viewrequisition($storesrequisitionuuid, $initiatorid)
    {
        $this->viewfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->viewrequisitionmodal=true;
    }
    public function initiateapproval($storesrequisitionuuid, $initiatorid, $approvalstatus)
    {
        $this->deliveryrequisitionuuid= $storesrequisitionuuid;
        $this->deliveryfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->deliveryinitiatorid= $initiatorid;
        $this->isapproved= $approvalstatus;
        $this->approvalrequisitionmodal=true;
    }

    public function deliverrequisition()
    {
        $this->validate(['adminvalidatorcomment'=>'required']);
        if(!$this->isapproved) {
            //update hod record status
            $updatehodrecord=$this->storesrequisitionService->updatehodrecord($this->deliveryrequisitionuuid, ['comment' => $this->adminvalidatorcomment,'decision'=>false]);
        }else{
            //update hod record status
            $updatehodrecord=$this->storesrequisitionService->updatehodrecord($this->deliveryrequisitionuuid, ['comment' => $this->adminvalidatorcomment,'decision'=>true]);
        }
        if($updatehodrecord['status']=='error'){ return $this->toast($updatehodrecord['status'], $updatehodrecord['message']);}
        if(!$this->isapproved) {
            //update stores requisition status
            $updatestoresrequisition=$this->storesrequisitionService->updatestoresrequisitionrecord($this->deliveryrequisitionuuid, ['status'=>'R','requisitionitems'=>json_encode($this->deliveryfields)]);
        }else{
            //update stores requisition status
            $updatestoresrequisition=$this->storesrequisitionService->updatestoresrequisitionrecord($this->deliveryrequisitionuuid, ['status'=>'A','requisitionitems'=>json_encode($this->deliveryfields)]);
        }

        if($updatestoresrequisition['status']=='error')
        {
            return $this->toast($updatestoresrequisition['status'], $updatestoresrequisition['message']);
        }
        //send email notification to initiator
        $initiator=$this->storesrequisitionService->getrecordowner($this->deliveryinitiatorid);
        $initiator->notify(new StoresrequisitionapprovalNotify($this->storesrequisitionService,$this->deliveryrequisitionuuid) );
        $this->toast($updatehodrecord['status'], $updatehodrecord['message']);
        $this->approvalrequisitionmodal=false;
        return $this->redirect(route('admin.workflows.approvals.deptstoresrequisitionapprovals'), navigate:true);
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.deptstoresrequisitionapprovals',[
            'headersforapproved' => $this->headersforapprovedrequisitions(),
            'totalawaitingapproval' => $this->getstoresrequisitionsawaitingapproval()->count(),     
            'totalawaitingissuing' => $this->getawaitingissuingstoresrequisitions()->count(),
            'totalawaitingclearance' => $this->getstoresrequisitionsawaitingclearance()->count(),
            'totaldelivered' => $this->getdeliveredstoresrequisitions()->count(),
            'totalrecieved' => $this->getrecievedstoresrequisitions()->count(), 
            'totalrejected'=>$this->getrejectedstoresrequisitions()->count(),
            'storesrequisitionsawaitingapproval'=>$this->getstoresrequisitionsawaitingapproval(),
            'storesrequisitionsdelivered'=>$this->getdeliveredstoresrequisitions(),        
            'storesrequisitionsrecieved'=>$this->getrecievedstoresrequisitions(),
            'storesrequisitionsrejected'=>$this->getrejectedstoresrequisitions(),
            'storesrequisitionsawaitingclearance'=>$this->getstoresrequisitionsawaitingclearance(),    
            'breadcrumbs'=>[['label' => 'Home', 'link' => route('admin.home')],['label' => "Stores Requisition Approvals"]],
        ]);
    }
}         