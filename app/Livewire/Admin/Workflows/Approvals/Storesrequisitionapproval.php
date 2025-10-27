<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\ihodstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iroleRepository;
use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\istoresrequisitionService;
use App\Notifications\StoresrequisitionapprovalNotify;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

class Storesrequisitionapproval extends Component
{
    Use Toast;
    protected $storesrequisitionService;
    public $storesrequisitionuuid, $approvalrecordid, $storesrequisitionitems=[];
    public $approver;
    public $employee;
    public $storesrequisition, $approvalrecord;
    public $comment, $isapproved;
    public $departmentid, $departmentname;

    public function boot(istoresrequisitionInterface $storesrequisitionrepo, iuserInterface $userrepo, ihodstoresrequisitionapprovalInterface $hodstoresrequisitionrepo, iroleRepository $rolerepo, idepartmentInterface $departmentrepo,
    istoresrequisitionService $storesrequisitionService)
    {
        $this->storesrequisitionService=$storesrequisitionService;
    }
    public function mount($approvalrecordid, $approvalitemuuid)
    {
        $this->storesrequisitionuuid=$approvalitemuuid;
        $this->approvalrecordid=$approvalrecordid;
        $this->storesrequisition=$this->storesrequisitionService->getstoresrequisition($this->storesrequisitionuuid);
        $this->storesrequisitionitems=$this->storesrequisitionService->getstoresrequisitionrequestitems($this->storesrequisitionuuid);
        $this->approvalrecord=$this->storesrequisitionService->gethodrequisitionapprovalrecord($this->approvalrecordid);
        $this->approver=$this->storesrequisitionService->getrecordowner($this->approvalrecord->user_id);
        $this->employee=$this->storesrequisitionService->getrecordowner($this->storesrequisition->initiator_id);
        $this->departmentid= $this->storesrequisitionService->getuserdepartmentid($this->employee->email);
        $this->departmentname= $this->storesrequisitionService->getuserdepartmentname($this->departmentid);
    }
    public function approverequisition()
    { 
        $initiator=$this->storesrequisitionService->getrecordowner($this->storesrequisition->initiator_id);
        $adminissuers=$this->rolerepo->getusersbyrole('Admin Issuer');


        $this->validate([
            'comment' => 'required'
        ]);
        $hodapprovalresponse=$this->storesrequisitionService->updatehodrecord($this->storesrequisitionuuid,[
            'comment'=>$this->comment,
            'decision'=>$this->isapproved
        ]);
        if($hodapprovalresponse['status'] !== 'success') {
           return $this->toast($hodapprovalresponse['status'], $hodapprovalresponse['message']);         
        }
        if(!$this->isapproved) {
            $requisitionapprovalresponse=$this->storesrequisitionService->updatestoresrequisitionrecord($this->storesrequisitionuuid,[
                'status'=>'R',
            ]);
            //send email notification to initiator
            $initiator->notify(new StoresrequisitionapprovalNotify($this->storesrequisitionService, $this->storesrequisitionuuid) );
            return $this->toast($requisitionapprovalresponse['status'], $requisitionapprovalresponse['message']);
        }else{
            $requisitionapprovalresponse=$this->storesrequisitionService->updatestoresrequisitionrecord($this->storesrequisitionuuid,[
                'status'=>'A',
            ]);
            //send email notification to approver
            $adminissuers->each(function($adminissuer) {
                $issuer=$this->storesrequisitionService->getrecordowner($adminissuer->id);
                $issuer->notify(new StoresrequisitionapprovalNotify($this->storesrequisitionService, $this->storesrequisitionuuid) );
            });
            $initiator->notify(new StoresrequisitionapprovalNotify($this->storesrequisitionService, $this->storesrequisitionuuid) );
            $this->toast($requisitionapprovalresponse['status'], $requisitionapprovalresponse['message']);
            return $this->redirect(route('admin.workflows.approvals.storesrequisitiondelivery'), navigate:true);
        
        }
    }

    #[Layout('layouts.plain')]
    public function render()
    {
        return view('livewire.admin.workflows.approvals.storesrequisitionapproval');
    }
}
