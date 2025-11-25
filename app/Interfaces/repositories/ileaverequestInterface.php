<?php

namespace App\Interfaces\repositories;

interface ileaverequestInterface
{
    public function getleaverequests();
    public function getleaverequestbyuuid($leaverequestuuid);
    public function getleaverequestsbyuserid($userid);
    public function getfirstleaverequestsbyuserid($userid);
    public function getlastleaverequestsbyuserid($userid);
    public function getleaverequestbyuseridandstatus($userid,$status);
    public function getleaverequestbystatus($status);
    public function getleaverequestsbyleavetype($leavetypeid);
    public function getleaverequest($id);
    public function createleaverequest($userid,$data);
    public function updateleaverequest($id, $data);
    public function deleteleaverequest($id);
}