<?php

namespace App\Livewire\Admin\Workflows\Approvals;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\iuserInterface;
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

    public function boot(ileaverequestInterface $leaverequestrepo, ileaverequestapprovalInterface $leaverequestapprovalrepo, ileavestatementInterface $leavestatementrepo, iuserInterface $userrepo, ileavetypeInterface $leavetyperepo, idepartmentInterface $departmentrepo)
    {
        $this->leaverequestrepo=$leaverequestrepo;
        $this->leaverequestapprovalrepo=$leaverequestapprovalrepo;
        $this->leavestatementrepo=$leavestatementrepo;
        $this->userrepo=$userrepo;
        $this->leavetyperepo=$leavetyperepo;
        $this->departmentrepo=$departmentrepo;
    } 
    public function mount($approvalrecordid, $approvalitemuuid)
    { 
        $this->approvalrecordid=$approvalrecordid;
        $this->leaverequestuuid=$approvalitemuuid;
        //Get leave record and appplication record 
        $this->requestrecord= $this->leaverequestrepo->getleaverequestByUuid($this->leaverequestuuid);
        $this->approvalrecord= $this->leaverequestapprovalrepo->getleaverequestapproval($this->leaverequestuuid);
        $this->leavetyperecord=$this->leavetyperepo->getleavetype($this->requestrecord->leavetype_id);
        $this->userstatement=$this->leavestatementrepo->getleavestatementByUserAndLeaveType($this->requestrecord->user_id, $this->requestrecord->leavetype_id);
        $this->approver=$this->userrepo->getuser($this->requestrecord->actinghod_id);
        $this->employee=$this->userrepo->getuser($this->requestrecord->user_id);
        $this->departmentid = $this->userrepo->getuserbyemail($this->employee->email)->department;
        $this->departmentname= $this->departmentrepo->getdepartment($this->departmentid)->first()->name;    
    }

    public function getAttachmentSrc()
    {    
        $documentpath=$this->requestrecord->attachment_src;  
        if(!Storage::exists($documentpath))
        { 
            abort(404,'Document not found');
        }   
        return Storage::url($documentpath);
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
        //Updates application action
        ($approved===true) ? $this->approvalrecord->update(['action'=>'A']): $this->approvalrecord->update(['action'=>'R']);
        //Updates application
        ($this->approvalrecord->action==='A')? $this->approvalrecord->update(['decision'=>true]): $this->approvalrecord->update(['decision'=>false]);
        $this->approvalrecord->update([
            'comment'=>$this->comment
        ]);
        $this->approvalrecord->save();
        //Updates leave request
        ($this->approvalrecord->action==='A')? $this->requestrecord->update(['status'=>'A']): $this->requestrecord->update(['status'=>'R']);
        $this->requestrecord->save();
        //Update leave statement
        if($this->approvalrecord->action==='A'){
            $this->userstatement->update([
                'days'=>(int)$this->userstatement->days + (int)$this->requestrecord->daysappliedfor,
            ]);
            $this->userstatement->save();
            $this->updateHOD();
        }
        if($this->approvalrecord->action==='R'){
            $this->userstatement->update([
                'days'=>(int)$this->userstatement->days - (int)$this->requestrecord->daysappliedfor,
            ]);
            $this->userstatement->save();
        }
        $appliedleaverecord=$this->leaverequestrepo->getleaverequestByUuid($this->approvalrecord->leaverequest_uuid);
        $initiator=$this->userrepo->getuser($appliedleaverecord->user_id);
        $initiator->notify(new LeaverequestApproved($appliedleaverecord, $this->leavetyperepo, $this->leaverequestapprovalrepo));
        $this->comment=null;
        $this->toast('success', 'Application finalized successfully by your decision'); 
        return $this->redirect(route('admin.workflows.leaverequests'), navigate:true);
    }

    public function updateHOD()
    {
        if($this->requestrecord->actinghod_id!=null){
            $hod=$this->userrepo->getuser($this->requestrecord->actinghod_id);
            $hod->assignRole('Acting HOD');
            $hod->save();
        }
    }   

    #[Layout('layouts.plain')]
    public function render()
    {
        return view('livewire.admin.emailapproval',[
            'attachmenturl' => $this->getAttachmentSrc()
        ]);
    }
}
 