<?php

namespace App\Interfaces\repositories;

interface itsallowanceInterface
{
    public function getallowances($year, $search = null);

    public function getdeptallowances($year, $departmentId = null, $search = null);

    public function getalldeptallowances($year, $departmentId = null, $search = null);

    public function getallallowances($year);

    public function getallowance($id);

    public function getallowancebyuuid($uuid);

    public function getallowancesbyapplicant($userId, $year, $search = null);

    public function getallowancesbystatus($year, $status);

    public function getallowancesbyworkflowparameter($year);

    public function createallowance($data);

    public function updateallowance($id, $data);

    public function deleteallowance($id);

    public function submitallowance($id);

    public function recommend($id, $data);

    public function rejectrecommendation($id, $data);

    public function approve($id, $data);

    public function reject($id, $data);

    public function sendback($id, $data);

    public function verifyfinance($id, $data);

    public function processpayment($id, $data);
}
