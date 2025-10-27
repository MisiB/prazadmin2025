<?php

namespace App\Interfaces\repositories;

interface ileaverequestapprovalInterface
{
    public function getleaverequestapprovals();
    public function getleaverequestapprovalsByUserId($userid);
    public function getleaverequestapprovalsByStatus($status);
    public function getleaverequestapproval($requestuuid);
    public function createleaverequestapproval($data);
    public function updateleaverequestapproval($id, $data);
    public function deleteleaverequestapproval($id);
}

 