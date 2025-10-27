<?php

namespace App\Interfaces\repositories;

interface iissuerstoresrequisitionapprovalInterface
{
    public function getissuerrequisitionapprovals();
    public function getissuerrequisitionapproval($storesrequisitionuuid);
    public function createissuerrequisitionapproval($data);
    public function updateissuerrequisitionapproval($id, $data);
    public function deleteissuerrequisitionapproval($id);
}
