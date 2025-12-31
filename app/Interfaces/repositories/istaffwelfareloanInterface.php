<?php

namespace App\Interfaces\repositories;

interface istaffwelfareloanInterface
{
    public function getloans($year, $search = null);

    public function getdeptloans($year, $departmentId = null, $search = null);

    public function getallloans($year);

    public function getloan($id);

    public function getloanbyuuid($uuid);

    public function getloansbyapplicant($userId, $year, $search = null);

    public function getloansbystatus($year, $status);

    public function getloansbyworkflowparameter($year);

    public function createloan($data);

    public function updateloan($id, $data);

    public function deleteloan($id);

    public function submitloan($id);

    public function approve($id, $data);

    public function reject($id, $data);

    public function capturehrdata($id, $data);

    public function executepayment($id, $data);

    public function acknowledgedebt($id, $data);

    // Config methods
    public function getActiveConfig();

    public function getConfig($id);

    public function createConfig($data);

    public function updateConfig($id, $data);

    // Calculation methods
    public function calculateExistingLoanBalance($userId);

    public function calculateLoanRepayment($principal, $interestRate, $months);
}
