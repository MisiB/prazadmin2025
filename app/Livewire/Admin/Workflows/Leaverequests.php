<?php

namespace App\Livewire\Admin\Workflows;


use App\Interfaces\services\ileaverequestService;
use App\Models\Leaverequestapproval;
use App\Models\Leavestatement;
use App\Notifications\LeaverequestSubmission;
use App\Notifications\LeaverequestSubmitted;
use App\Notifications\LeaverequestWithdrawn;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Leaverequests extends Component
{
    
    use Toast, WithFileUploads, WithPagination;
    protected $leaverequestService, $user;
    public $breadcrumbs = [];
    public $addleaverequestmodal=false;
    public $statuslist =[], $statusfilter;
    public $dateRangeConfig=[];
    public $firstname, $surname, $employeenumber;
    public $selectedleavetypeid, $selectedapprover;
    public $reasonforleave, $supportingdoc, $addressonleave;
    public $starttoenddate, $startdate, $enddate, $returndate, $daysappliedfor, $validdays;
    public $usereporttoid, $leaveapprovername;
    public $searchuuid, $datesrange;
    public $assignedhodid;

    public function boot(ileaverequestService $leaverequestService)
    {
        $this->leaverequestService=$leaverequestService;
        $this->user=Auth::user();
    }

    public function mount(){
        $this->breadcrumbs=[
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => "Leave Requests"]
        ];
        $this->statuslist =[
            ['id' => 'P','name' => 'Pending'],
            ['id' => 'A','name' => 'Approved'],
            ['id' => 'C','name' => 'Cancelled'],
            ['id' => 'R','name' => 'Rejected']
        ];
        $this->loaddatesrange();
        $this->firstname=$this->user->name;
        $this->surname=$this->user->surname;
        if(!$this->user->department)
        {
            abort(403, 'Access Denied. Please liaise with ICT for your Department assignment');
        }
        if(!$this->user->department->reportto)
        {
            abort(403, 'Access Denied. Please contact ICT to complete your department HOD assignment.');
        }
        $this->usereporttoid= $this->user->department->reportto;
        $this->leaveapprovername=$this->getrequestapprovername();
    }
    public function updated()
    {
        $this->validaterequest();
    }

    public function initiateleaveaddition()
    {
        $this->loaddatesrange();
        $this->addleaverequestmodal=true;
    }
    public function gethodrepresentatives()
    {
        $departmentid=$this->leaverequestService->getuserdepartmentid($this->user->email);
        return $this->leaverequestService->gethodrepresentatives($departmentid, $this->user->id);
    }
    public function getdatesrange()
    {
        //Get all start dates of existing leave requests
        $firstuserleaverequest=$this->leaverequestService->getfirstleaverequestsbyuserid($this->user->id);
        $lastuserleaverequest=$this->leaverequestService->getlastleaverequestsbyuserid($this->user->id);
        if($firstuserleaverequest==null && $lastuserleaverequest==null)
        {
            $datesrange=[];
        }
        else{
            $existingstartdate= $firstuserleaverequest->startdate;
            $existingreturndate= $lastuserleaverequest->returndate;
            $period = CarbonPeriod::create($existingstartdate, $existingreturndate);
            $datesrange = $period->toArray();
        }
        return $datesrange;
    }
    public function loaddatesrange()
    { 
        $this->datesrange= $this->getdatesrange();
        $this->dateRangeConfig=[
            "mode"=>"range",
            'disable' => $this->datesrange
        ];
    }
    public function validaterequest()
    {
        $this->validate([
            'selectedleavetypeid'=>'required',
            'starttoenddate'=>'required'
        ]);
        //check if user has a pending request
        $userstatement=$this->leaverequestService->getleavestatementbyuserandleavetype($this->user->id,$this->selectedleavetypeid);
        if($userstatement!=null)
        {
            $ceilingdays=$this->leaverequestService->getleavetype($this->selectedleavetypeid)->ceiling;
            $this->validdays=$ceilingdays-$userstatement->daystaken;
            $starttoenddatearray=explode(' to ',$this->starttoenddate);
            $this->startdate=Carbon::parse($starttoenddatearray[0])->format('Y-m-d');
            $this->enddate=Carbon::parse($starttoenddatearray[1])->format('Y-m-d');
            $this->returndate=Carbon::parse($this->enddate)->copy()->nextWeekday()->format('Y-m-d');
            $leavePeriod=CarbonPeriod::create($this->startdate, $this->enddate);
            //Vacation , Sick, Maternity leaves include weekends
            $vacationleaveid=$this->leaverequestService->getleavetypebyname('Vacation')->id;
            $sickleaveid=$this->leaverequestService->getleavetypebyname('Sick')->id;
            $maternityleaveid=$this->leaverequestService->getleavetypebyname('Maternity')->id;
            if((int)$this->selectedleavetypeid===$vacationleaveid || (int)$this->selectedleavetypeid===$sickleaveid || (int)$this->selectedleavetypeid===$maternityleaveid){
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
        return $this->leaverequestService->getleaverequestbyuseridandstatus($this->user->id, 'P', $this->statusfilter,$this->searchuuid);
    }
    public function getapprovedleaverequests()
    {
        return $this->leaverequestService->getleaverequestbyuseridandstatus($this->user->id, 'A', $this->statusfilter,$this->searchuuid);
    }
    public function getrejectedleaverequests()
    {
        return $this->leaverequestService->getleaverequestbyuseridandstatus($this->user->id, 'R', $this->statusfilter,$this->searchuuid);
    }
    public function getcancelledleaverequests()
    {
        return $this->leaverequestService->getleaverequestbyuseridandstatus($this->user->id, 'C', $this->statusfilter,$this->searchuuid);
    }
    public function getmyleaverequests()
    {
        return $this->leaverequestService->getleaverequestsbyuserid($this->user->id,$this->statusfilter,$this->searchuuid);
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
            ['label' => 'Acting Person', 'key' => 'hod'],  
            ['label' => 'Approving H.O.D', 'key' => 'approver'], 
        ];
    }

    public function getleavetypes()
    {
        $leavetypes=$this->leaverequestService->getleavetypes();

        $filteredleavetypes=$leavetypes->filter(function ($leavetype)
        {
            return !(strtolower($leavetype->name) === 'maternity' && strtolower($this->user->gender) === 'm');
        });

        $map=$filteredleavetypes->map(function ($leavetype)
        {
            return ['id'=>$leavetype->id, 'name'=>$leavetype->name];    
        });
        return $map;
    }

    public function getrequestapprovername()
    {
       return $this->leaverequestService->getuserfullname($this->usereporttoid);
    }

    public function sendleaverequest()
    {
        $this->validate([
            'employeenumber'=>'required|numeric',
            'reasonforleave'=>'required',
            'addressonleave'=>'required',
        ]);


        $delayfrom=now();
        $userid=$this->user->id;
        $selectedleavetypeid=$this->selectedleavetypeid;
        $leavedetails=[];
        $leavedetails['daysappliedfor']=$this->daysappliedfor;
        $leavedetails['validdays']=$this->validdays;
        $leavedetails['startdate']=$this->startdate;
        $leavedetails['enddate']=$this->enddate;
        $leavedetails['returndate']=$this->returndate;
        $leavedetails['addressonleave']=$this->addressonleave;
        $leavedetails['reasonforleave']=$this->reasonforleave;
        $leavedetails['actinghodid']=$this->assignedhodid;
        $supportingdoc=$this->supportingdoc;
        $hodactiveonleaveresponse=$this->leaverequestService->isactiveonleave($this->usereporttoid);
        $usereporttoid = ($hodactiveonleaveresponse['status']==true)?$hodactiveonleaveresponse['actinghodid']:$this->usereporttoid;
        $approver=$this->leaverequestService->getuser($usereporttoid);

        
        $sendresponse=$this->leaverequestService->sendleaverequest($userid, $selectedleavetypeid, $usereporttoid, $leavedetails, $supportingdoc); 
        if($sendresponse['status']==='warning'||$sendresponse['status']==='error' )
        {
            return $this->toast($sendresponse['status'],$sendresponse['message']);
        }
        $leaverequestid=$sendresponse['message'];
        $this->addleaverequestmodal=false;
        $this->toast($sendresponse['status'], 'Leave request submitted & email notification sent');
        return $this->redirect(route('admin.workflows.leaverequests'));
    }

    public function cancelrequest($leaverequestuuid)
    {
        $leaverequestcopy=$this->leaverequestService->getleaverequestbyuuid($leaverequestuuid);
        $updateresponse=$this->leaverequestService->updateleaverequest($leaverequestuuid, [
            'status'=>'C',
        ]);
        if($updateresponse['status']=='error')
        {
            return $this->toast('error', $updateresponse['message']);
        }
        //Update the leaverequestapproval record
        $approvalrecordupdate=$this->leaverequestService->updateleaverequestapproval($leaverequestuuid, [
            'action'=>'C',
            'comment'=>'Request cancelled by user'
        ]);
        if($approvalrecordupdate['status']=='error')
        {
            return $this->toast('error', $approvalrecordupdate['message']);
        }
        //Update the leavestatement record
        $leavestatement=$this->leaverequestService->getleavestatementbyuserandleavetype($this->user->id, $leaverequestcopy->leavetype_id);
        if($leaverequestcopy->status==='A')
        {
            $leavestatementupdate=$this->leaverequestService->updateleavestatement($leavestatement->id, [
                'daystaken'=>(float)$leavestatement->daystaken-(float)$leaverequestcopy->daysappliedfor
            ]);
            if($leavestatementupdate['status']=='error')
            {
                return $this->toast('error', $leavestatementupdate['message']);
            }
        }

        //Notify the approver of the recall via email
        $requestapprovalrecord=$this->leaverequestService->getleaverequestapproval($leaverequestuuid);
        if($requestapprovalrecord==null)
        {
            return $this->toast('error', 'No approval record found for this request');
        }
        $approver=$this->leaverequestService->getuser($requestapprovalrecord->user_id);
        $appliedleaverecord=$this->leaverequestService->getleaverequestbyuuid($leaverequestuuid);
        $approver->notify(new LeaverequestWithdrawn($this->leaverequestService, $leaverequestuuid));
        $this->toast($updateresponse['status'], $updateresponse['message']);
        $this->addleaverequestmodal=false;
        return $this->redirect('/workflows/leaverequests');
    }

    public function getmyleavestatementbalances()
    {
        $leavestatementbalances=[];

        $this->leaverequestService->getleavetypes()->map(function ($leavetype) use (&$leavestatementbalances){
            $daysattained=( (float)$this->leaverequestService->getleavestatementbyuseridandleavename($this->user->id, $leavetype->name)?->daysattained) ?? 0;
            $daystaken=( (float)$this->leaverequestService->getleavestatementbyuseridandleavename($this->user->id, $leavetype->name)?->daystaken ) ?? 0;  
            $leavestatementbalances[]=[
                strtolower($leavetype->name)=>$daysattained - $daystaken,
            ];
        });
        $leavestatementbalances=array_merge(...$leavestatementbalances);
        return $leavestatementbalances;
    }
    

    public function render()
    {
        return view('livewire.admin.workflows.leaverequests',[
            'totalcancelled'=>$this->getcancelledleaverequests()->count(),
            'totalpending'=>$this->getpendingleaverequests()->count(),
            'totalapproved'=>$this->getapprovedleaverequests()->count(),
            'totalrejected'=>$this->getrejectedleaverequests()->count(),
            'leaverequests'=>$this->getmyleaverequests(),
            'leavetypesmap'=>$this->getleavetypes(),
            'headers' => $this->headers(),
            'hodassigneesmap'=>$this->gethodrepresentatives(),
            'leavestatementbalances'=>$this->getmyleavestatementbalances()
        ]);
    }
}