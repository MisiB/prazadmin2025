<?php

namespace App\Interfaces\repositories;

interface ihodstoresrequisitionapprovalInterface
{
    public function gethodrequisitionapprovals();
    public function gethodrequisitionapproval($storesrequisitionuuid);
    public function createhodrequisitionapproval($data);
    public function updatehodrequisitionapproval($id, $data);
    public function deletehodrequisitionapproval($id);
}
 