<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\iadminstoresrequisitionapprovalInterface;
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
use App\Notifications\StoresrequisitionverificationSubmitted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class Storesrequisitiondelivery extends Component
{
    Use Toast;
    protected $storesrequisitionService, $user;
    public $storestabs="delivery-tab";
    public $statuslist =[];
    public $addrequisitionmodal=false, $requisitionverificationmodal=false, $deliveryrequisitionmodal=false, $viewrequisitionmodal=false;
    public $itemdetail, $requiredquantity, $purposeofrequisition;
    public $hodid, $departmentid=0, $searchuuid;
    public $issuerquantity, $issuercomment, $adminvalidatorcomment, $isapproved=false;
    public $deliveryrequisitionuuid, $deliveryinitiatorid, $deliveryissuerid, $adminvalidatorid;
    public $itemfields = [], $viewfields=[], $deliveryfields=[]; // Number of items in the requisition form

    public function boot(istoresrequisitionService $storesrequisitionService)
    {
        $this->storesrequisitionService=$storesrequisitionService;
        $this->user=Auth::user();
    }

    public function mount()
    {
        $this->hodid=$this->user->department->reportto;
        $this->itemfields[] = [
            'itemdetail' => '',
            'requiredquantity' => ''
        ];
        $this->statuslist=[
            ['id'=>'P', 'name'=>'Pending'],
            ['id'=>'A', 'name'=>'Approved'],
            ['id'=>'O', 'name'=>'Opened'],
            ['id'=>'D', 'name'=>'Delivered'],
            ['id'=>'C', 'name'=>'Received'],
            ['id'=>'R', 'name'=>'Rejected'],
         ]; 
    }

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
    public function getapprovedstoresrequisitions()
    {
        return $this->storesrequisitionService->getapprovedstoresrequisitions($this->departmentid,$this->searchuuid=$this->searchuuid);
    }
    public function getopenedstoresrequisitions()
    {
        return $this->storesrequisitionService->getopenedstoresrequisitions($this->departmentid,$this->searchuuid=$this->searchuuid);
    }
    public function getclearedstoresrequisitions()
    {
        return $this->storesrequisitionService->getstoresrequisitionsawaitingclearance($this->departmentid,$this->searchuuid);
    }
    public function getdeliveredstoresrequisitions()
    {
        return $this->storesrequisitionService->getdeliveredstoresrequisitions($this->departmentid,$this->searchuuid);
    }
    public function getrecievedstoresrequisitions()
    {
        return $this->storesrequisitionService->getrecievedstoresrequisitions($this->departmentid,$this->searchuuid);
    }
    public function getrejectedstoresrequisitions()
    {
        return $this->storesrequisitionService->getrejectedstoresrequisitions($this->departmentid,$this->searchuuid);
    }

    public function viewrequisition($storesrequisitionuuid, $initiatorid)
    {
        $this->viewfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->viewrequisitionmodal=true;
    }
    public function openrequisition($storesrequisitionuuid, $initiatorid)
    {
        $createissuerrecord=$this->issuerstoresrequisitionapprovalrepo->createissuerrequisitionapproval([
            'storesrequisition_uuid'=>$storesrequisitionuuid,
            'user_id'=>$this->user->id
        ]);
        if($createissuerrecord['status']=='error')
        {
            return $this->toast($createissuerrecord['status'], $createissuerrecord['message']);
        }
        //send email notification to initiator
        $statusupdate=$this->storesrequisitionService->updatestoresrequisitionrecord($storesrequisitionuuid, ['status'=>'O']);
        if($statusupdate['status']=='error')
        {
            return $this->toast($statusupdate['status'], $statusupdate['message']);
        }
        $createreceiverrecord=$this->storesrequisitionService->createreceiverrequisitionapprovalrecord([
            'storesrequisition_uuid'=>$storesrequisitionuuid,
            'user_id'=>$this->user->id
        ]);
        if($createreceiverrecord['status']=='error')
        {
            return $this->toast($createreceiverrecord['status'], $createreceiverrecord['message']);
        }
        $initiator=$this->storesrequisitionService->getrecordowner($initiatorid);
        $hod=$this->storesrequisitionService->getrecordowner($this->hodid);
        $initiator->notify(new StoresrequisitionopeningNotification($this->storesrequisitionService, $storesrequisitionuuid) );
        $hod->notify(new StoresrequisitionopeningNotification($this->storesrequisitionService, $storesrequisitionuuid) );
        $this->toast($createissuerrecord['status'], $createissuerrecord['message']);
        return $this->redirect(route('admin.workflows.approvals.storesrequisitiondelivery'), navigate:true);
    }
    public function initiateverification($storesrequisitionuuid, $initiatorid)
    {
        $this->deliveryrequisitionuuid= $storesrequisitionuuid;
        $this->deliveryfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->deliveryinitiatorid= $initiatorid;
        $this->deliveryissuerid=$this->storesrequisitionService->getissuerrequisitionapprovalrecord($this->deliveryrequisitionuuid)->user_id;
        $deliveryissueremail=$this->storesrequisitionService->getrecordowner($this->deliveryissuerid)->email;
        $this->adminvalidatorid= $this->storesrequisitionService->gethodidforuser($deliveryissueremail);
        $this->requisitionverificationmodal=true;
    }
    public function sendrequisitionforverification()
    {
        $this->validate([
            'issuercomment'=>'required',
            'deliveryfields.*.issuedquantity'=>'required|numeric',
        ]);

        $updateissuerrecord=$this->storesrequisitionService->updateissuerrequisitionrecord($this->deliveryrequisitionuuid, [
            'comment' => $this->issuercomment,
            'decision'=>true
        ]);
        if($updateissuerrecord['status']=='error')
        {
            return $this->toast($updateissuerrecord['status'], $updateissuerrecord['message']);
        }
        //update stores requisition status
        $updatestoresrequisition=$this->storesrequisitionService->updatestoresrequisitionrecord($this->deliveryrequisitionuuid, [
            'status'=>'V',
            'requisitionitems'=>json_encode($this->deliveryfields),
        ]);
        if($updatestoresrequisition['status']=='error')
        {
            return $this->toast($updatestoresrequisition['status'], $updatestoresrequisition['message']);
        }
        //create adminstoresrequisitionapprovalrepo
        $createadminstoresrequisitionapproval=$this->storesrequisitionService->createadminrequisitionapprovalrecord([
            'storesrequisition_uuid'=>$this->deliveryrequisitionuuid,
            'user_id'=>$this->adminvalidatorid
        ]);
        if($createadminstoresrequisitionapproval['status']=='error')
        {
            return $this->toast($createadminstoresrequisitionapproval['status'], $createadminstoresrequisitionapproval['message']);
        }
        //send email notification to admin validator
        $adminvalidator=$this->storesrequisitionService->getrecordowner($this->adminvalidatorid);
        $adminvalidator->notify(new StoresrequisitionverificationSubmitted($this->storesrequisitionService, $this->deliveryrequisitionuuid) );
        $this->toast($updateissuerrecord['status'], $updateissuerrecord['message']);
        $this->requisitionverificationmodal=false;
        return $this->redirect(route('admin.workflows.approvals.storesrequisitiondelivery'), navigate:true);
    }
    public function initiatedelivery($storesrequisitionuuid, $initiatorid, $approvalstatus)
    {
        $this->deliveryrequisitionuuid= $storesrequisitionuuid;
        $this->deliveryfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->deliveryinitiatorid= $initiatorid;
        $this->deliveryissuerid=$this->storesrequisitionService->getissuerrequisitionapprovalrecord($this->deliveryrequisitionuuid)->user_id;
        $deliveryissueremail=$this->storesrequisitionService->getrecordowner($this->deliveryissuerid)->email;
        $this->adminvalidatorid= $this->storesrequisitionService->gethodidforuser($deliveryissueremail);
        $this->isapproved=$approvalstatus;
        $this->deliveryrequisitionmodal=true;
    }
    public function deliverrequisition()
    {
        
        $this->validate([
            'adminvalidatorcomment'=>'required',
        ]);
        //update admin record status
        if(!$this->isapproved) {
            $updatevalidatorrecord=$this->storesrequisitionService->updateadminrequisitionrecord($this->deliveryrequisitionuuid, [
                'comment' => $this->adminvalidatorcomment,
                'decision'=>false
            ]);
        }else{
            $updatevalidatorrecord=$this->storesrequisitionService->updateadminrequisitionrecord($this->deliveryrequisitionuuid, [
                'comment' => $this->adminvalidatorcomment,
                'decision'=>true
            ]);
        }
        if($updatevalidatorrecord['status'] !== 'success') {
           return $this->toast($updatevalidatorrecord['status'], $updatevalidatorrecord['message']);         
        }   
        //update stores requisition
        if(!$this->isapproved) {
            $updatestoresrequisition=$this->storesrequisitionService->updatestoresrequisitionrecord($this->deliveryrequisitionuuid, [
                'status'=>'R',
                'requisitionitems'=>json_encode($this->deliveryfields),
            ]);
        }else{
            $updatestoresrequisition=$this->storesrequisitionService->updatestoresrequisitionrecord($this->deliveryrequisitionuuid, [
                'status'=>'D',
                'requisitionitems'=>json_encode($this->deliveryfields),
            ]);
        }
        if($updatestoresrequisition['status']=='error')
        {
            return $this->toast($updatestoresrequisition['status'], $updatestoresrequisition['message']);
        }
        //send email notification to initiator
        $initiator=$this->storesrequisitionService->getrecordowner($this->deliveryinitiatorid);
        $initiator->notify(new StoresrequisitiondeliveryNotification($this->storesrequisitionService, $this->deliveryrequisitionuuid) );
        $this->toast($updatevalidatorrecord['status'], $updatevalidatorrecord['message']);
        $this->deliveryrequisitionmodal=false;
        return $this->redirect(route('admin.workflows.approvals.storesrequisitiondelivery'), navigate:true);
    }


    /**export stores requisition csv report */
    public function exportstoresrequisitionreport($status)
    {
        $export=$this->storesrequisitionService->exportdata($status);
        if($export['status']=='error')
        {
            return $this->toast($export['status'], $export['message']);
        }

        $filename='storesrequisitionreport_as_at_'.Carbon::now()->format('Y_M_d').'.csv';
        $file=fopen($filename, 'w');
        foreach($export['data'] as $items)
        {
            fputcsv($file, $items);
        }
        fclose($file);
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.admin.workflows.approvals.storesrequisitiondelivery',[    
            'headersforapproved' => $this->headersforapprovedrequisitions(),
            'storesrequisitionsawaitingdelivery'=>$this->getapprovedstoresrequisitions(),           
            'totalapproved' => $this->getapprovedstoresrequisitions()->count(),
            'storesrequisitionsopened'=>$this->getopenedstoresrequisitions(),
            'totalopened' => $this->getopenedstoresrequisitions()->count(),
            'storesrequisitionsawaitingclearance'=>$this->getclearedstoresrequisitions(),
            'totalawaiting' => $this->getclearedstoresrequisitions()->count(),
            'storesrequisitionsdelivered'=>$this->getdeliveredstoresrequisitions(),
            'totaldelivered' => $this->getdeliveredstoresrequisitions()->count(),
            'storesrequisitionsrecieved'=>$this->getrecievedstoresrequisitions(),
            'totalrecieved' => $this->getrecievedstoresrequisitions()->count(), 
            'storesrequisitionsrejected'=>$this->getrejectedstoresrequisitions(),
            'totalrejected'=>$this->getrejectedstoresrequisitions()->count(),
            'breadcrumbs'=>[['label' => 'Home', 'link' => route('admin.home')], ['label' => "Stores Requisition Approvals"]]
        ]);
    }

}


