<?php

namespace App\Interfaces\repositories;

interface ileaverequestInterface
{
    public function getleaverequests();
    public function getleaverequestByUuid($leaverequestuuid);
    public function getleaverequestsByUserId($userid);
    public function getleaverequestsByLeavetype($leavetypeid);
    public function getleaverequestByUserIdAndStatus($userid,$status);
    public function getleaverequestByStatus($status);
    public function getleaverequest($id);
    public function createleaverequest($userid,$data);
    public function updateleaverequest($id, $data);
    public function deleteleaverequest($id);
}
