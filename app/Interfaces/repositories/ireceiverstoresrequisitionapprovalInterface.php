<?php

namespace App\Interfaces\repositories;

interface ireceiverstoresrequisitionapprovalInterface
{
    public function getreceiverrequisitionapprovals();
    public function getreceiverrequisitionapproval($storesrequisitionuuid);
    public function createreceiverrequisitionapproval($data);
    public function updatereceiverrequisitionapproval($id, $data);
    public function deletereceiverrequisitionapproval($id);

}
