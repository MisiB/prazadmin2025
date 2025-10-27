<?php

namespace App\Interfaces\repositories;

interface istoresrequisitionInterface
{
    public function getstoresrequisitions();
    public function getmystoresrequisitions($userid);
    public function getdeptstoresrequisitions($status);
    public function gethodstoresrequestssubmissions();
    public function getstoresrequisitionsbystatus($status);
    public function getstoresrequisitionsbydepartment($departmentid);
    public function getstoresrequisition($storesrequisitionuuid);
    public function createstoresrequisition($data);
    public function updatestoresrequisition($id, $data);
    public function deletestoresrequisition($id);
    public function exportstoresrequisitionreport($status);
}
