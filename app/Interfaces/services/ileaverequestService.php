<?php

namespace App\Interfaces\services;

use Illuminate\Support\Collection;

interface ileaverequestService
{
    public function getleaverequests();
    public function getleaverequestbyuuid($leaverequestuuid);
    public function getleaverequestsbyuserid($userid,$statusfilter=null,$searchuuid=null);
    public function getfirstleaverequestsbyuserid($userid);
    public function getlastleaverequestsbyuserid($userid);
    public function getleaverequestsbyleavetype($leavetypeid);
    public function getleaverequestbyuseridandstatus($userid,$status,$statusfilter=null,$searchuuid=null);
    public function getleaverequestbystatus($status);
    public function getleaverequest($id);
    public function createleaverequest($userid,$data);
    public function updateleaverequest($id, $data);
    public function deleteleaverequest($id);

    public function getleavestatements();
    public function getleavestatementByLeaveType($leavetypeid);
    public function getleavestatementByUser($userid);
    public function getleavestatement($id);
    public function createleavestatement($data);
    public function updateleavestatement($id, $data);
    public function deleteleavestatement($id);
    public function getleavestatementbyuserandleavetype($userid, $leavetypeid);
    public function getleavestatementbyuseridandleavename($userid, $leavename);

    public function getleavetypes();
    public function getleavetypebyname($name);
    public function getleavetype($id);
    public function createleavetype($data);
    public function updateleavetype($id, $data);
    public function deleteleavetype($id);

    public function getleaverequestapprovals();
    public function getleaverequestapprovalsbyuserid($userid);
    public function getleaverequestapprovalsbystatus($status);
    public function getleaverequestapproval($requestuuid);
    public function createleaverequestapproval($data);
    public function updateleaverequestapproval($uuid, $data);
    public function deleteleaverequestapproval($id);

    public function getuser($userid);
    public function getuserfullname($userid);
    public function getuserbyemail($email);
    public function getusers();
    public function sendleaverequest($userid, $selectedleavetypeid, $usereporttoid, array $leavedetails, $supportingdoc=null);
    public function getuserdepartmentid($useremail);
    public function getuserdepartmentname($userdepartmentid);
    public function gethodrepresentatives($departmentid,$hodid);
    public function isactiveonleave($userid);
 
}
 