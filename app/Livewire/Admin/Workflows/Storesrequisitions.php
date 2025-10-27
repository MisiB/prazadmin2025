<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\ihodstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iissuerstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\ireceiverstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\istoresrequisitionService;
use App\Notifications\StoresrequisitionacceptanceNotification;
use App\Notifications\StoresrequisitionapprovalSubmitted;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class Storesrequisitions extends Component
{
    Use Toast;

    protected $storesrequisitionService, $user;
    public $hodassigneesmap=[];
    public $statuslist =[], $statusfilter;
    public $addrequisitionmodal=false, $deliveryrequisitionmodal=false, $viewrequisitionmodal=false, $acceptancerequisitionmodal=false, $isaccepted=false, $recallrequisitionmodal=false;
    public $itemdetail, $requiredquantity, $purposeofrequisition;
    public $hodid, $adminissuerid;
    public $issuerquantity, $issuercomment;
    public $deliveryrequisitionuuid, $deliveryinitiatorid;
    public $itemfields = [], $viewfields=[], $deliveryfields=[]; // Number of items in the requisition form
    public $searchuuid, $storesrequisitions;

    public function boot(istoresrequisitionService $storesrequisitionService)
    {
        $this->storesrequisitionService=$storesrequisitionService;
        $this->user=Auth::user();
    }

    public function mount()
    {
        $this->itemfields[] = [
            'itemdetail' => '',
            'requiredquantity' => ''
        ];
        $this->hodid=$this->user->department->reportto;
        $this->statuslist=[
            ['id'=>'P', 'name'=>'Pending'],
            ['id'=>'A', 'name'=>'Approved'],
            ['id'=>'O', 'name'=>'Opened'],
            ['id'=>'D', 'name'=>'Delivered'],
            ['id'=>'C', 'name'=>'Received'],
            ['id'=>'R', 'name'=>'Rejected'],
        ];
        $this->hodassigneesmap=[];
        $deptmembers=$this->storesrequisitionService->getdeptmembersbydepartmentid($this->user->department->department_id);
        $deptmembers->each(function($member){
            $this->hodassigneesmap[]=[
                'id'=>$member->id,
                'name'=>$member->name.' '.$member->surname
            ];
        });
        $this->storesrequisitions=$this->getmystoresrequests();
    }

    public function updated()
    {
        $this->storesrequisitions=$this->getmystoresrequests();
    }

    public function addrequisitionitem()
    {
        $this->itemfields[] = [
            'itemdetail' => '',
            'requiredquantity' => ''
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
    public function getmystoresrequests()
    { 
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,$this->statusfilter,$this->searchuuid);
    }
    public function getpendingstoresrequisitions()
    {
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,'P');
    }
    public function getapprovedstoresrequisitions()
    {
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,'A');
    }
    public function getopenstoresrequisitions()
    {
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,'O');
    }
    public function getdeliveredstoresrequisitions()
    {
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,'D');
    }
    public function getrecievedstoresrequisitions()
    {
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,'C');
    }
    public function getrejectedstoresrequisitions()
    {
        return $this->storesrequisitionService->getmystoresrequisitions($this->user->id,'R');
    }

    public function sendrequisition()
    {
   
        $this->validate([
            'itemfields.*.itemdetail' => 'required',
            'itemfields.*.requiredquantity' => 'required|numeric',
            'purposeofrequisition'=>'required'
        ]);
        $storesrequuid= (string) Str::uuid();

        $createrequisition=$this->storesrequisitionService->createstoresrequisitionrecord([
            'storesrequisition_uuid'=>$storesrequuid,
            'requisitionitems'=>json_encode($this->itemfields),
            'purposeofrequisition'=>$this->purposeofrequisition,
            'status'=>'P',
            'initiator_id'=>$this->user->id
        ]);
        if($createrequisition['status']=='success')
        {
            $createrequisition=$this->storesrequisitionService->createhodrequisitionapprovalrecord([
                'storesrequisition_uuid'=>$storesrequuid,
                'user_id'=>$this->hodid
            ]);
            //send email notification to approver
            $approver=$this->storesrequisitionService->getrecordowner($this->hodid);
            $approver->notify(new StoresrequisitionapprovalSubmitted($this->storesrequisitionService, $storesrequuid) );
            $this->toast($createrequisition['status'], $createrequisition['message']);
            $this->addrequisitionmodal=false;
            return $this->redirect('/workflows/storesrequisitions', navigate:true);
        }
        return $this->toast($createrequisition['status'], $createrequisition['message']);
    }
    public function viewrequisition($storesrequisitionuuid, $initiatorid)
    {
        $this->viewfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->viewrequisitionmodal=true;
    }

    public function initiateacceptance($storesrequisitionuuid, $initiatorid, $approvalstatus)
    {
        $this->deliveryrequisitionuuid= $storesrequisitionuuid;
        $this->deliveryfields = $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $issuerrecord=$this->storesrequisitionService->getissuerrequisitionapprovalrecord($storesrequisitionuuid);
        $this->adminissuerid=$this->storesrequisitionService->getrecordowner($issuerrecord->user_id);
        $this->isaccepted= $approvalstatus;
        $this->acceptancerequisitionmodal=true;
    }

    public function acceptrequisition()
    {
        //update stores record 
        if(!$this->isaccepted) {
            $updaterequisitionrecord=$this->storesrequisitionService->updatestoresrequisition($this->deliveryrequisitionuuid, [
                'status' => 'R'
            ]);
        }else{ 
            $updaterequisitionrecord=$this->storesrequisitionService->updatestoresrequisition($this->deliveryrequisitionuuid, [
                'status' => 'C'
            ]);
        }
        if($updaterequisitionrecord['status'] !== 'success') {
           return $this->toast($updaterequisitionrecord['status'], $updaterequisitionrecord['message']);         
        } 
        //send email notification to approver and issuer
        $adminissuer=$this->storesrequisitionService->getrecordowner($this->adminissuerid);
        $adminissuer->notify(new StoresrequisitionacceptanceNotification($this->storesrequisitionService, $this->deliveryrequisitionuuid) );
        $hod=$this->storesrequisitionService->getrecordowner($this->hodid);
        $hod->notify(new StoresrequisitionacceptanceNotification($this->storesrequisitionService, $this->deliveryrequisitionuuid) );
        $this->toast($updaterequisitionrecord['status'], $updaterequisitionrecord['message']);
        $this->acceptancerequisitionmodal=false;
        return $this->redirect(route('admin.workflows.storesrequisitions'), navigate:true);
    }    

    public function initiaterecall($storesrequisitionuuid)
    {
        $this->deliveryrequisitionuuid= $storesrequisitionuuid;
        $this->deliveryfields =  $this->storesrequisitionService->getstoresrequisitionrequestitems($storesrequisitionuuid);
        $this->recallrequisitionmodal=true;
    }

    public function recallrequisition()
    {
        $updaterequisitionrecord=$this->storesrequisitionService->updatestoresrequisitionrecord($this->deliveryrequisitionuuid, [
            'status' => 'R'
        ]);
        if($updaterequisitionrecord['status'] !== 'success') {
           return $this->toast($updaterequisitionrecord['status'], $updaterequisitionrecord['message']);         
        } 
        //send email notification to approver and issuer
        $hod=$this->storesrequisitionService->getrecordowner($this->hodid);
        $hod->notify(new StoresrequisitionacceptanceNotification($this->storesrequisitionService, $this->deliveryrequisitionuuid) );
        $this->toast($updaterequisitionrecord['status'], $updaterequisitionrecord['message']);
        $this->recallrequisitionmodal=false;
        return $this->redirect(route('admin.workflows.storesrequisitions'), navigate:true);
    }   

    public function render()
    {
        return view('livewire.admin.workflows.storesrequisitions',[
            'headersforpending' => $this->headersforpendingrequisitions(),
            'storesrequisitionscount'=>$this->getmystoresrequests()->count(),
            'hodstoresrequisitionscount'=>$this->storesrequisitionService->gethodstoresrequestssubmissions()->count(),
            'totalpending' => $this->getpendingstoresrequisitions()->count(),
            'totalapproved' => $this->getapprovedstoresrequisitions()->count(),
            'totalopened' => $this->getopenstoresrequisitions()->count(),
            'totaldelivered' => $this->getdeliveredstoresrequisitions()->count(),
            'totalrecieved' => $this->getrecievedstoresrequisitions()->count(), 
            'totalrejected'=>$this->getrejectedstoresrequisitions()->count(),
            'breadcrumbs' => [['label' => 'Home', 'link' => route('admin.home')], ['label' => "Stores Requisition"]]
        ]);
    }
}