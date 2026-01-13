<?php

namespace App\Interfaces\repositories;

interface ipaymentrequisitionInterface
{
    public function getpaymentrequisitions($year, $search = null);

    public function getpaymentrequisitionsbyapplicant($userId, $year, $search = null);

    public function getpaymentrequisition($id);

    public function getpaymentrequisitionbyuuid($uuid);

    public function getpaymentrequisitionbydepartment($year, $department_id, $search = null);

    public function createpaymentrequisition($data);

    public function updatepaymentrequisition($id, $data);

    public function deletepaymentrequisition($id);

    public function getpaymentrequisitionbystatus($year, $status);

    public function submitpaymentrequisition($id);

    public function recommendhod($id, $data);

    public function reviewadmin($id, $data);

    public function recommendadmin($id, $data);

    public function approvefinal($id, $data);

    public function reject($id, $data);

    public function getpaymentrequisitionsbyworkflowparameter($year);
}
