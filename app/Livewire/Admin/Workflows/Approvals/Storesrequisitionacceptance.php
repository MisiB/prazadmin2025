<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\services\istoresrequisitionService;
use App\Notifications\StoresrequisitionacceptanceNotification;
use App\Notifications\StoresrequisitionapprovalNotify;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

class Storesrequisitionacceptance extends Component
{
    Use Toast;
    protected $storesrequisitionService;
    public $storesrequisitionuuid, $approvalrecordid, $storesrequisitionitems=[];
    public $employee;
    public $hod;
    public $adminissuer, $adminvalidator;
    public $storesrequisition, $approvalrecord, $issuerrecord, $receiverrecord, $validatorrecord;
    public $comment, $isapproved;
    public $departmentid, $departmentname;

    public function boot(istoresrequisitionService $storesrequisitionService)
    {
        $this->storesrequisitionService=$storesrequisitionService;
    }
    public function mount($approvalrecordid, $approvalitemuuid)
    {
        $this->storesrequisitionuuid=$approvalitemuuid;
        $this->approvalrecordid=$approvalrecordid;
        $this->storesrequisition=$this->storesrequisitionService->getstoresrequisition($this->storesrequisitionuuid);
        $this->issuerrecord=$this->storesrequisitionService->getissuerrequisitionapprovalrecord($this->storesrequisitionuuid);
        $this->validatorrecord=$this->storesrequisitionService->getadminrequisitionapprovalrecord($this->storesrequisitionuuid);
        $this->storesrequisitionitems=json_decode($this->storesrequisition->requisitionitems, true);
        $this->approvalrecord=$this->storesrequisitionService->gethodrequisitionapprovalrecord($this->approvalrecordid);
        $this->receiverrecord=$this->storesrequisitionService->getreceiverrequisitionapprovalrecord($this->approvalrecordid);
        $this->employee=$this->userrepo->getuser($this->storesrequisition->initiator_id);
        $this->departmentid= $this->storesrequisitionService->getuserdepartmentid($this->employee->email);
        $this->departmentname= $this->storesrequisitionService->getuserdepartmentname($this->departmentid);
        $this->hod=$this->storesrequisitionService->getrecordowner($this->approvalrecord->user_id);
        $this->adminissuer=$this->storesrequisitionService->getrecordowner($this->issuerrecord->user_id);
        $this->adminvalidator=$this->storesrequisitionService->getrecordowner($this->validatorrecord->user_id);
    }
    public function acceptrequisition()
    { 
        $adminissuers=$this->rolerepo->getusersbyrole('Admin Issuer');
        $hod=$this->storesrequisitionService->getrecordowner($this->hod->id);

        $this->validate([
            'comment' => 'required',
        ]);
        $receiverapprovalresponse=$this->storesrequisitionService->updatereceiverrecord($this->storesrequisitionuuid,[
            'comment'=>$this->comment,
            'decision'=>$this->isapproved
        ]);
        if($receiverapprovalresponse['status'] !== 'success') {
           return $this->toast($receiverapprovalresponse['status'], $receiverapprovalresponse['message']);         
        }
        if(!$this->isapproved) {
            $requisitionapprovalresponse=$this->storesrequisitionService->updatestoresrequisitionrecord($this->storesrequisitionuuid,[
                'status'=>'R',
            ]);
            //send email notification to issuer
            $adminissuers->each(function($adminissuer) {
                $issuer=$this->userrepo->getuser($adminissuer->id);
                $issuer->notify(new StoresrequisitionacceptanceNotification($this->storesrequisitionService, $this->storesrequisitionuuid) );
            });
            return $this->toast($requisitionapprovalresponse['status'], $requisitionapprovalresponse['message']);
        }else{
            $requisitionapprovalresponse=$this->storesrequisitionService->updatestoresrequisitionrecord($this->storesrequisitionuuid,[
                'status'=>'C',
            ]);
            //send email notification to approver
            $adminissuers->each(function($adminissuer) {
                $issuer=$this->userrepo->getuser($adminissuer->id);
                $issuer->notify(new StoresrequisitionacceptanceNotification($this->storesrequisitionService, $this->storesrequisitionuuid) );
            });
            $hod->notify(new StoresrequisitionacceptanceNotification($this->storesrequisitionService, $this->storesrequisitionuuid) );
            $this->toast($requisitionapprovalresponse['status'], $requisitionapprovalresponse['message']);
            return $this->redirect(route('admin.workflows.approvals.storesrequisitiondelivery'), navigate:true);
        }
    }

    #[Layout('layouts.plain')]
    public function render()
    {
        return view('livewire.admin.workflows.approvals.storesrequisitionacceptance');
    }
}
