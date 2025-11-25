<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\services\ileaverequestService;
use App\Notifications\LeaverequestApproved;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

class Emailapproval extends Component
{
    use Toast;
    protected $approvalrecordid, $leaverequestuuid;
    protected $leaverequestrepo, $leavestatementrepo, $userrepo, $leaverequestapprovalrepo, $leavetyperepo, $departmentrepo;
    protected $leaverequestService;
    protected $hodrole='Acting HOD';
    public $requestrecord;
    public $approvalrecord;
    public $leavetyperecord;
    public $approver;
    public $employee;
    public $userstatement;
    public $comment;
    public $isapproved;
    public $viewattachmentmodal=false;
    public $departmentid;
    public $departmentname;

    public function boot(ileaverequestService $leaverequestService)
    {
        $this->leaverequestService=$leaverequestService;
    } 
    public function mount($approvalrecordid, $approvalitemuuid)
    { 
        $this->approvalrecordid=$approvalrecordid;
        $this->leaverequestuuid=$approvalitemuuid;
        //Get leave record and appplication record 
        $this->requestrecord= $this->leaverequestService->getleaverequestbyuuid($this->leaverequestuuid);
        $this->approvalrecord= $this->leaverequestService->getleaverequestapproval($this->leaverequestuuid);
        $this->leavetyperecord=$this->leaverequestService->getleavetype($this->requestrecord->leavetype_id);
        $this->userstatement=$this->leaverequestService->getleavestatementbyuserandleavetype($this->requestrecord->user_id, $this->requestrecord->leavetype_id);
        $this->approver=$this->leaverequestService->getuser($this->approvalrecord->user_id);
        $this->employee=$this->leaverequestService->getuser($this->requestrecord->user_id);
        $this->departmentid = $this->leaverequestService->getuserdepartmentid($this->employee->email);
        $this->departmentname= $this->leaverequestService->getuserdepartmentname($this->departmentid);    
    }

    public function getAttachmentSrc()
    {    
        $documentpath=$this->requestrecord->attachment_src; 
        if(!empty($documentpath) )
        {
            if(!Storage::exists($documentpath))
            { 
                abort(404,'Document not found');
            }   
            return Storage::url($documentpath);
        }
        return $documentpath;

    }

    public function processApplication()
    {        
        $this->validate([
            'comment'=>'required',
        ]);
        $this->toast('success', 'Processing your leave application');
        $this->approveApplication($this->isapproved);
    }

    public function approveApplication( bool $approved)
    {   
        $actinghoduser=null;
        if(!$this->approver->hasRole('Acting HOD'))
        {
            $this->toast("warning", "Access denied due to HOD's return"); 
            return $this->redirect(route('admin.workflows.leaverequests'));
        }
        //Updates application action
        ($approved===true) ? $this->approvalrecord->update(['action'=>'A']): $this->approvalrecord->update(['action'=>'R']);
        //Updates application
        ($this->approvalrecord->action==='A')? $this->approvalrecord->update(['decision'=>true]): $this->approvalrecord->update(['decision'=>false]);
        $this->approvalrecord->update([
            'comment'=>$this->comment
        ]);
        $this->approvalrecord->save();
        if($this->requestrecord->actinghod_id!=null)
        {
            $actinghoduser=$this->leaverequestService->getuser($this->requestrecord->actinghod_id);
        }

        //Updating the leave request and statement
        if($this->requestrecord->status==='P'&& $this->approvalrecord->action==='A')
        {
            $this->userstatement->update([
                'days'=>(int)$this->userstatement->days + (int)$this->requestrecord->daysappliedfor,
            ]);
            $this->userstatement->save();
            $this->updateHOD();
            $this->requestrecord->update(['status'=>'A']);
            $this->requestrecord->save();
            if($actinghoduser!=null){$actinghoduser->assignRole($this->hodrole);}
        }elseif($this->requestrecord->status==='A'&& $this->approvalrecord->action==='R'){
            $this->userstatement->update([
                'days'=>(int)$this->userstatement->days - (int)$this->requestrecord->daysappliedfor,
            ]);
            $this->userstatement->save();
            $this->requestrecord->update(['status'=>'C']);
            $this->requestrecord->save();
            if($actinghoduser!=null){$actinghoduser->removeRole($this->hodrole);}
        }else{
            $this->requestrecord->update(['status'=>'R']);
            $this->requestrecord->save();
        }

        $appliedleaverecord=$this->leaverequestService->getleaverequestbyuuid($this->approvalrecord->leaverequest_uuid);
        $initiator=$this->leaverequestService->getuser($appliedleaverecord->user_id);
        $initiator->notify(new LeaverequestApproved($this->leaverequestService, $this->approvalrecord->leaverequest_uuid));
        $this->comment=null;
        $this->toast('success', 'Application finalized successfully by your decision'); 
        return $this->redirect(route('admin.workflows.leaverequests'));
    }

    public function updateHOD()
    {
        if($this->requestrecord->actinghod_id!=null){
            $hod=$this->leaverequestService->getuser($this->requestrecord->actinghod_id);
            $hod->assignRole('Acting HOD');
            $hod->save();
        }
    }   

    #[Layout('layouts.plain')]
    public function render()
    {
        return view('livewire.admin.workflows.approvals.emailapproval',[
            'attachmenturl' => $this->getAttachmentSrc()??''
        ]);
    }
}
 