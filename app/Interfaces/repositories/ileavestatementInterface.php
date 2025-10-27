<?php

namespace App\Interfaces\repositories;

interface ileavestatementInterface
{
    public function getleavestatements();
    public function getleavestatementByLeaveType($leavetypeid);
    public function getleavestatementByUser($userid);
    public function getleavestatement($id);
    public function createleavestatement($data);
    public function updateleavestatement($id, $data);
    public function deleteleavestatement($id);
    public function getleavestatementByUserAndLeaveType($userid, $leavetypeid);
    public function getleavestatementByUserIdAndLeaveName($userid, $leavename);
}
