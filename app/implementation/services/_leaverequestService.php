<?php

namespace App\implementation\services;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\services\ileaverequestService;
use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Notifications\LeaverequestSubmission;
use App\Notifications\LeaverequestSubmitted;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class _leaverequestService implements ileaverequestService
{
    /**
     * Create a new class instance.
     */
    //use WithFileUploads;
    protected $leaverequestrepo;
    protected $leavetyperepo;
    protected $userrepo;
    protected $leavestatmentrepo;
    protected $leaverequestapprovalrepo;
    protected $departmentrepo;
    protected $currentdate;

    public function __construct(ileaverequestInterface $leaverequestrepo, ileavetypeInterface $leavetyperepo, iuserInterface $userrepo, 
    ileavestatementInterface $leavestatmentrepo, ileaverequestapprovalInterface $leaverequestapprovalrepo, idepartmentInterface $departmentrepo)
    {
        $this->leaverequestrepo = $leaverequestrepo;
        $this->leavetyperepo = $leavetyperepo;
        $this->userrepo = $userrepo;
        $this->leavestatmentrepo = $leavestatmentrepo;
        $this->leaverequestapprovalrepo = $leaverequestapprovalrepo;
        $this->departmentrepo = $departmentrepo;
        $this->currentdate=Carbon::now();
    }

    public function getleaverequests()
    {
        return $this->leaverequestrepo->getleaverequests();
    }
    public function getleaverequestbyuuid($leaverequestuuid)
    { 
        return $this->leaverequestrepo->getleaverequestbyuuid($leaverequestuuid);
    }
    public function getleaverequestsbyuserid($userid,$statusfilter=null,$searchuuid=null)
    {
        return $this->leaverequestrepo->getleaverequestsbyuserid($userid,$statusfilter,$searchuuid);
    }
    public function getfirstleaverequestsbyuserId($userid)
    {
        return $this->leaverequestrepo->getfirstleaverequestsbyuserid($userid);

    }
    public function getlastleaverequestsbyuserid($userid)
    {
        return $this->leaverequestrepo->getlastleaverequestsbyuserid($userid);
    }
    public function getleaverequestsbyleavetype($leavetypeid)
    {
        return $this->leaverequestrepo->getleaverequestsbyleavetype($leavetypeid);
    }
    public function getleaverequestbyuseridandstatus($userid,$status,$statusfilter=null,$searchuuid=null)
    {
        return $this->leaverequestrepo->getleaverequestbyuseridandstatus($userid, $status, $statusfilter,$searchuuid);
    }
    public function getleaverequestbystatus($status)
    {
        return $this->leaverequestrepo->getleaverequestbystatus($status);
    }
    public function getleaverequest($id)
    {
        return $this->leaverequestrepo->getleaverequest($id);
    }
    public function createleaverequest($userid,$data)
    {
        return $this->leaverequestrepo->createleaverequest($userid,$data);
    }
    public function updateleaverequest($id, $data)
    {
        return $this->leaverequestrepo->updateleaverequest($id, $data);
    }
    public function deleteleaverequest($id)
    {
        return $this->leaverequestrepo->deleteleaverequest($id);
    }

    public function getleavestatements()
    {
        return $this->leavestatmentrepo->getleavestatements();
    }



    public function getleavestatementbyleavetype($leavetypeid)
    {
        return $this->leavestatmentrepo->getleavestatementbyleavetype($leavetypeid);
    }
    public function getleavestatementbyuser($userid)
    {
        return $this->leavestatmentrepo->getleavestatementbyuser($userid);
    }
    public function getleavestatement($id)
    {
        return $this->leavestatmentrepo->getleavestatement($id);
    }
    public function createleavestatement($data)
    {
        return $this->leavestatmentrepo->createleavestatement($data);
    }
    public function updateleavestatement($id, $data)
    {
        return $this->leavestatmentrepo->updateleavestatement($id, $data);
    }
    public function deleteleavestatement($id)
    {
        return $this->leavestatmentrepo->deleteleavestatement($id);
    }
    public function getleavestatementbyuserandleavetype($userid, $leavetypeid)
    {
        return $this->leavestatmentrepo->getleavestatementbyuserandleavetype($userid, $leavetypeid);
    }
    public function getleavestatementbyuseridandleavename($userid, $leavename)
    {
        return $this->leavestatmentrepo->getleavestatementbyuseridandleavename($userid, $leavename);
    }

    public function getleavetypes()
    {
        return $this->leavetyperepo->getleavetypes();
    }
    public function getleavetypebyname($name)
    {
        return $this->leavetyperepo->getleavetypebyname($name);
    }
    public function getleavetype($id)
    {
        return $this->leavetyperepo->getleavetype($id);
    }
    public function createleavetype($data)
    {
        return $this->leavetyperepo->createleavetype($data);
    }
    public function updateleavetype($id, $data)
    {
        return $this->leavetyperepo->updateleavetype($id, $data);
    }
    public function deleteleavetype($id)    
    {
        return $this->leavetyperepo->deleteleavetype($id);
    }



    public function getleaverequestapprovals()
    {
        return $this->leaverequestapprovalrepo->getleaverequestapprovals();
    }
    public function getleaverequestapprovalsbyuserid($userid)
    {
        return $this->leaverequestapprovalrepo->getleaverequestapprovalsbyuserid($userid);
    }
    public function getleaverequestapprovalsbystatus($status)
    {
        return $this->leaverequestapprovalrepo->getleaverequestapprovalsbystatus($status);
    }
    public function getleaverequestapproval($requestuuid)  
    {
        return $this->leaverequestapprovalrepo->getleaverequestapproval($requestuuid);
    }
    public function createleaverequestapproval($data)
    {
        return $this->leaverequestapprovalrepo->createleaverequestapproval($data);
    }
    public function updateleaverequestapproval($uuid, $data)
    {
        return $this->leaverequestapprovalrepo->updateleaverequestapproval($uuid, $data);
    }
    public function deleteleaverequestapproval($id)
    {
        return $this->leaverequestapprovalrepo->deleteleaverequestapproval($id);
    }


    public function getuser($userid)
    {
       return $this->userrepo->getuser($userid);    
    }    
    public function getuserfullname($userid)
    {
       $hod=$this->userrepo->getuser($userid);
       $approvername=$hod->name.' '.$hod->surname;
       return $approvername;
    }
    public function getuserbyemail($email)
    {
        return $this->userrepo->getuserbyemail($email);
    }
    public function getusers()
    {
        return $this->userrepo->getusers();
    }

    public function sendleaverequest($userid, $selectedleavetypeid, $usereporttoid, array $leavedetails, $supportingdoc=null)
    {
        //Allow valid days check bypass for compassionate leave
        $user=$this->getuser($userid);
        $compassionateleave=$this->getleavetypebyname('Compassionate');
        $selectedleavetype=$this->getLeavetype($selectedleavetypeid);
        if(strtolower($user->gender==='m') && strtolower($selectedleavetype==='maternity'))
        {  
           return ['status'=>'warning', 'message'=>'Male employees are not allowed to apply for Marternity Leave'];
        }
        if($leavedetails['daysappliedfor'] > $leavedetails['validdays'] && $selectedleavetype->name!==$compassionateleave->name ){
           return ['status'=>'warning', 'message'=>'You have exceeded the number of days','You are only entitled to a maximum of '.$leavedetails['validdays'].' days '];
        }
        
        
        $activeleaveresponse=$this->isactiveonleave($userid);
        if($activeleaveresponse['status']===true){
            return ['status'=>'warning', 'message'=>$activeleaveresponse['message']];
        }else{
            $requestuuid= (string) Str::uuid();
            $fileuuid= (string) Str::uuid();
            //create leave approval record Action=>APPROVE|REJECT Decision=>true|false|null
            $createrequest=$this->leaverequestrepo->createleaverequest($user->id,[   
                'leaverequestuuid'=>$requestuuid,
                'user_id'=>$user->id,
                'leavetype_id'=>$selectedleavetypeid,
                'startdate'=>$leavedetails['startdate'],
                'enddate'=>$leavedetails['enddate'],
                'returndate'=>$leavedetails['returndate'],
                'daysappliedfor'=>$leavedetails['daysappliedfor'],
                'addressonleave'=>$leavedetails['addressonleave'],
                'reasonforleave'=>$leavedetails['reasonforleave'],
                'attachment_src'=>($supportingdoc!=null)?$supportingdoc->storeAs('leaveattachments',$fileuuid):null,
                'status'=>'P',
                'year'=>date('Y'),
                'actinghod_id'=>$leavedetails['actinghodid']??null,
            ]); 
            if($createrequest['status']=='error')
            {
                return ['status'=>'error', 'message'=>$createrequest['message']];
            } 
            $createapprovalrecord=$this->createleaverequestapproval([
                'leaverequest_uuid'=>$requestuuid,
                'user_id'=>$usereporttoid,
                'action'=>'PENDING'
            ]); 
            if($createapprovalrecord['status']=='error')
            {
                return ['status'=>'error', 'message'=>$createrequest['message']]; 
            }
            /** Do notifications */
            $usereporttoid= $user->department->reportto;
            $hodactiveonleaveresponse=$this->isactiveonleave($usereporttoid);
            //Assign current HOD
            $usereporttoid = ($hodactiveonleaveresponse['status']==true)?$hodactiveonleaveresponse['actinghodid']:$usereporttoid;
            $approver=$this->getuser($usereporttoid);

            //Notify approver
            if ($approver) {
                $approver->notify( new LeaverequestSubmitted($this, $requestuuid)->delay(now()->copy()->addSeconds(1)) );
            }
            //Notify default approver if need there is an Acting HOD assigned
            if($hodactiveonleaveresponse['status']==true)
            {
                $assignedhod=$this->getuser($user->department->reportto);
                if ($assignedhod!=null) {
                    $assignedhod->notify( new LeaverequestSubmitted($this, $requestuuid)->delay(now()->copy()->addSeconds(2)) );
                }
            }
            //Notify user
            $user->notify( new LeaverequestSubmission($this, $requestuuid)->delay(now()->copy()->addSeconds(1)) );
            
            return ['status'=>'success', 'message'=>$requestuuid, 'actinghodid'=>$leavedetails['actinghodid']??null] ;
        }
    }
    public function getuserdepartmentid($useremail)
    {
        return $this->userrepo->getuserbyemail($useremail)->department->department_id;
    }
    public function getuserdepartmentname($userdepartmentid)
    {
        return $this->departmentrepo->getdepartment($userdepartmentid)->name;
    }

    public function gethodrepresentatives($departmentid,$hodid)
    {
        $represantativesmap=[];
        $this->departmentrepo->getusers($departmentid)->each(function($user) use (&$represantativesmap, &$hodid){
            if($user->user_id != $hodid)
            {
                $deptuser=$this->userrepo->getuser($user->user_id);
                $represantativesmap[]=['id'=>$deptuser->id,'name'=>$deptuser->name.' '.$deptuser->surname];
            }
        });
        return $represantativesmap;
    }

    public function isactiveonleave($userid)
    {
        $response=null;
        $this->getleaverequestsbyuserid($userid,'A')->each(function($leaverequest) use(&$response){
            $returndate=Carbon::parse($leaverequest->returndate);
            if($returndate->greaterThan($this->currentdate))
            {
                $response=($leaverequest->actinghod_id!=null)? ['status'=>true, 'message'=>'User has an active leave application', 'actinghodid'=>$leaverequest->actinghod_id]:['status'=>true, 'message'=>'User has an active leave application'];
            }
        });
        if($response!=null)
        {
            return $response;
        }
        return['status'=>false, 'message'=>'User has no active leave application'];

    }
}
  