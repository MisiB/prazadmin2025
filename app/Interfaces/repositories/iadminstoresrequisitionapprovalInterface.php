<?php

namespace App\Interfaces\repositories;

interface iadminstoresrequisitionapprovalInterface
{
    public function getadminrequisitionapprovals();
    public function getadminrequisitionapproval($storesrequisitionuuid);
    public function createadminrequisitionapproval($data);
    public function updateadminrequisitionapproval($id, $data);
    public function deleteadminrequisitionapproval($id);
}
