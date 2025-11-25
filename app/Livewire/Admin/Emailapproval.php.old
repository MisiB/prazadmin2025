<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\iuserInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

class Emailapproval extends Component
{
    use Toast;
    protected $approvalrecordid, $leaverequestuuid;
    protected $leaverequestrepo, $leavestatementrepo, $userrepo, $leaverequestapprovalrepo, $leavetyperepo;

    public $requestrecord;
    public $approvalrecord;
    public $leavetyperecord;
    public $approver;
    public $employee;
    public $userstatement;
    public $comment;
    public $signature;
    public $isapproved;
    

    public function boot(ileaverequestInterface $leaverequestrepo, ileaverequestapprovalInterface $leaverequestapprovalrepo, ileavestatementInterface $leavestatementrepo, iuserInterface $userrepo, ileavetypeInterface $leavetyperepo)
    {
        $this->leaverequestrepo=$leaverequestrepo;
        $this->leaverequestapprovalrepo=$leaverequestapprovalrepo;
        $this->leavestatementrepo=$leavestatementrepo;
        $this->userrepo=$userrepo;
        $this->leavetyperepo=$leavetyperepo;
    } 
    public function mount($approvalrecordid, $leaverequestuuid)
    {
        $this->approvalrecordid=$approvalrecordid;
        $this->leaverequestuuid=$leaverequestuuid;
        //Get leave record and appplication record
        $this->requestrecord= $this->leaverequestrepo->getleaverequestByUuid($this->leaverequestuuid);
        $this->approvalrecord= $this->leaverequestapprovalrepo->getleaverequestapproval($this->leaverequestuuid);
        $this->leavetyperecord=$this->leavetyperepo->getleavetype($this->requestrecord->leavetype_id);
        $this->userstatement=$this->leavestatementrepo->getleavestatementByUserAndLeaveType($this->requestrecord->user_id, $this->requestrecord->leavetype_id);
        $this->approver=$this->userrepo->getuser($this->requestrecord->actinghod_id);
        $this->employee=$this->userrepo->getuser($this->requestrecord->user_id);
        
    }
 

    public function processApplication()
    {        
        $this->validate([
            'comment'=>'required',
            'signature'=>'required'
            
        ]);
        $this->toast('success', 'Processing your leave application');
        $this->approveApplication($this->isapproved);
    }

    public function approveApplication( bool $approved)
    {   
        //Updates application action
        ($approved===true) ? $this->approvalrecord->update(['action'=>'A']): $this->approvalrecord->update(['action'=>'R']);
        //Updates application
        ($this->approvalrecord->action=='A')? $this->approvalrecord->update(['decision'=>true]): $this->approvalrecord->update(['decision'=>false]);
        $this->approvalrecord->update([
            'comment'=>$this->comment,
            'signature'=>$this->signature
        ]);
        $this->approvalrecord->save();
        //Updates leave request
        ($this->approvalrecord->action=='A')? $this->requestrecord->update(['status'=>'A']): $this->requestrecord->update(['status'=>'R']);
        $this->requestrecord->save();
        //Update leave statement
        if($this->approvalrecord->action=='A'){
            $this->userstatement->update([
                'days'=>(int)$this->userstatement->days + (int)$this->requestrecord->daysappliedfor,
            ]);
            $this->userstatement->save();
            $this->updateHOD();
        }
        if($this->approvalrecord->action=='R'){
            $this->userstatement->update([
                'days'=>(int)$this->userstatement->days - (int)$this->requestrecord->daysappliedfor,
            ]);
            $this->userstatement->save();
        }
        $this->comment=null;
        $this->signature=null;
        return $this->toast('success', 'Application finalized successfully by your decision'); 
    }

    public function updateHOD()
    {
        if($this->requestrecord->actinghod_id!=null){
            $hod=$this->userrepo->getuser($this->requestrecord->actinghod_id);
            $hod->assignRole('Acting HOD');
            $hod->save();
        }
    }

    public function download($attachmentSrc)
    {
        return response()->download(storage_path('\\app\\private\\'.$attachmentSrc));
    }    

    #[Layout('layouts.plain')]
    public function render()
    {
        return view('livewire.admin.emailapproval');
    }
}
 