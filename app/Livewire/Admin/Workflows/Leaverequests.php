<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Models\Leaverequestapproval;
use App\Models\Leavestatement;
use App\Notifications\LeaverequestSubmitted;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class Leaverequests extends Component
{
    
    use Toast;
    protected $repository;
    protected $workplanrepository;
    public $year;
    public $search;
    public $title;
    public $priority;
    public $individualoutputbreakdown_id;
    public $description;
    public $status;
    public $start_date;
    public $end_date;
    public $contribution;
    public $id;
    public $statusfilter;
    public $priorityfilter;
    public $selectedtask=null;
    public $analysisstatus;
    public $analysiscomment;

    public $totaloverdue=0;
    public $totaldue=0; 
    public bool $addtaskmodal=false;
    public bool $viewtaskmodal=false;
    public bool $link = false;

    protected $leaverequestrepo, $leavetyperepo, $userrepo, $leavestatmentrepo, $leaverequestapprovalrepo;
    protected $user;
    public $breadcrumbs = [];
    public $totalapproved=0, $totalpending=0, $totalrejected=0;
    public $addleaverequestmodal=false;
    public $statuslist =[];
    public $dateRangeConfig=[];
    public $hodassigneesmap=[], $leaveatypesmap=[];
    public $firstname, $surname, $employeenumber;
    public $selectedleavetypeid, $selectedapprover;
    public $reasonforleave, $supportingdoc, $addressonleave;   
    public $employeesignature;
    public $starttoenddate, $startdate, $enddate, $returndate, $daysappliedfor, $validdays;
    public $usereporttoid, $leaveapprovername;

    public function boot(ileaverequestInterface $leaverequestrepo, ileavetypeInterface $leavetyperepo, iuserInterface $userrepo, ileavestatementInterface $leavestatmentrepo, ileaverequestapprovalInterface $leaverequestapprovalrepo)
    {
        $this->leaverequestrepo = $leaverequestrepo;
        $this->leavetyperepo=$leavetyperepo;
        $this->userrepo=$userrepo;
        $this->leavestatmentrepo=$leavestatmentrepo;
        $this->leaverequestapprovalrepo=$leaverequestapprovalrepo;
        $this->user=Auth::user();
    }

    public function mount(){
        $this->breadcrumbs=[
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => "Leave Requests"]
        ];
        $this->year = Carbon::now()->year;
        $this->start_date = Carbon::now()->startOfWeek()->nextWeekday()->format('Y-m-d');
        $this->end_date = Carbon::now()->endOfWeek()->previousWeekday()->format('Y-m-d');
        $this->statuslist =[
            ['id' => 'PENDING','name' => 'Pending'],
            ['id' => 'APPROVED','name' => 'Approved'],
            ['id' => 'REJECTED','name' => 'Rejected']
        ];
        $this->dateRangeConfig=["mode"=>"range"];
        $this->firstname=$this->user->name;
        $this->surname=$this->user->surname;
        $this->usereporttoid= $this->user->department->reportto;
        $this->leaveapprovername=$this->getrequestapprovername();
    }

    public function updated()
    {
        $this->validaterequest();
    }
    public function validaterequest()
    {
        $this->validate([
            'selectedleavetypeid'=>'required',
            'starttoenddate'=>'required'
        ]);
        //check if user has a pending request
        $userstatement=$this->leavestatmentrepo->getleavestatementByUserAndLeaveType($this->user->id,$this->selectedleavetypeid);
        if($userstatement!=null)
        {
            $ceilingdays=$this->leavetyperepo->getleavetype($this->selectedleavetypeid)->ceiling;
            $this->validdays=$ceilingdays-$userstatement->days;
            $starttoenddatearray=explode(' to ',$this->starttoenddate);
            $this->startdate=Carbon::parse($starttoenddatearray[0])->format('Y-m-d');
            $this->enddate=Carbon::parse($starttoenddatearray[1])->format('Y-m-d');
            $this->returndate=Carbon::parse($this->enddate)->copy()->nextWeekday()->format('Y-m-d');
            $leavePeriod=CarbonPeriod::create($this->startdate, $this->enddate);
            $vacationleaveid=$this->leavetyperepo->getLeavetypeByName('Vacation')->id;
            if($this->selectedleavetypeid===$vacationleaveid){
                $this->daysappliedfor=$leavePeriod->count();
            }else{
                $leavePeriod=$leavePeriod->filter('isWeekday');
                $this->daysappliedfor=$leavePeriod->count();
            }
        }else{
            $this->starttoenddate=null;
            $this->startdate=null;
            $this->enddate=null;
            $this->returndate=null;
            $this->validdays=null;
            $this->daysappliedfor=null;
            $this->toast('warning','No leavestatement record found/ Select a leave type');
        }

    }
    public function getpendingleaverequests()
    {
        return $this->leaverequestrepo->getleaverequestByUserIdAndStatus($this->user->id, 'PENDING');
    }
    public function getapprovedleaverequests()
    {
        return $this->leaverequestrepo->getleaverequestByUserIdAndStatus($this->user->id, 'APPROVED');
    }
    public function getrejectedleaverequests()
    {
        return $this->leaverequestrepo->getleaverequestByUserIdAndStatus($this->user->id, 'REJECTED');
    }
    public function getmyleaverequests()
    {
        return $this->leaverequestrepo->getleaverequestsByUserId($this->user->id);
    }
    public function headers(): array
    { 
        return [
            ['label' => 'Leave type', 'key' => 'leavetype.name'],
            ['label' => 'Reason of leave', 'key' => 'reasonforleave'],
            ['label' => 'Start date', 'key' => 'startdate'],
            ['label' => 'End date', 'key' => 'enddate'],
            ['label' => 'Date of return', 'key' => 'returndate'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => 'Approving H.O.D', 'key' => 'hod'],  
        ];
    }

    public function getleavetypes()
    {
        $leavetypes=$this->leavetyperepo->getleavetypes();
        $map=$leavetypes->each(function ($leavetype)
        {
            return ['id'=>$leavetype->id, 'name'=>$leavetype->name];
        });
        return $map;
    }

    public function getrequestapprovername()
    {
       $hod=$this->userrepo->getuser($this->usereporttoid);
       $approvername=$hod->name.' '.$hod->surname;
       return $approvername;
    }

    public function sendleaverequest()
    {
        $this->validate([
            'employeenumber'=>'required|numeric',
            'reasonforleave'=>'required',
            'addressonleave'=>'required',
            'employeesignature'=>'required',
        ]);
        if($this->daysappliedfor > $this->validdays){
            $this->toast('warning','You have exceeded the number of days','You are only entitled to a maximum of '.$this->validNumberOfDays.' days ');
            return 0;
        }
        else{
            $requestuuid= (string) Str::uuid();
            $fileuuid= (string) Str::uuid();
            //create leave approval record Action=>APPROVE|REJECT Decision=>true|false|null
            $createrequest=$this->leaverequestrepo->createleaverequest($this->user->id,[   
                'leaverequestuuid'=>$requestuuid,
                'user_id'=>$this->user->id,
                'leavetype_id'=>$this->selectedleavetypeid,
                'startdate'=>$this->startdate,
                'enddate'=>$this->enddate,
                'returndate'=>$this->returndate,
                'daysappliedfor'=>$this->daysappliedfor,
                'addressonleave'=>$this->addressonleave,
                'reasonforleave'=>$this->reasonforleave,
                'attachment_src'=>($this->supportingdoc!=null)?$this->supportingdoc->store('leaveattachments/'.$fileuuid):null,
                'signature'=>$this->employeesignature,
                'status'=>'PENDING',
                'year'=>date('Y'),
                'actinghod_id'=>$this->usereporttoid,
            ]); 
            if($createrequest['status']=='error')
            {
                return $this->toast('error', $createrequest['message']);
            } 
            $createapprovalrecord=$this->leaverequestapprovalrepo->createleaverequestapproval([
                'leaverequest_uuid'=>$requestuuid,
                'user_id'=>$this->usereporttoid,
                'action'=>'PENDING',
                'signature'=>$this->employeesignature
            ]);
            //Notify the approver via email
            $approver=$this->userrepo->getuser($this->usereporttoid);
            $appliedleaverecord=$this->leaverequestrepo->getleaverequestByUuid($requestuuid);
            $approver->notify(new LeaverequestSubmitted($appliedleaverecord, $this->leavetyperepo, $this->leaverequestapprovalrepo));
            $this->toast($createapprovalrecord['status'], $createapprovalrecord['message']);
            $this->addleaverequestmodal=false;
            return redirect('/workflows/leaverequests');
        }
    }
    public function render()
    {
        return view('livewire.admin.workflows.leaverequests',[
            'totalpending'=>$this->getpendingleaverequests()?$this->getpendingleaverequests()->count():0,
            'totalapproved'=>$this->getapprovedleaverequests()?$this->getapprovedleaverequests()->count():0,
            'totalrejected'=>$this->getrejectedleaverequests()?$this->getrejectedleaverequests()->count():0,
            'leaverequests'=>$this->getmyleaverequests(),
            'leavetypesmap'=>$this->getleavetypes(),
            'headers' => $this->headers(),
        ]);
    }
}
