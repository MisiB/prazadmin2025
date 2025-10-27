<?php

namespace App\implementation\services;

use App\Interfaces\repositories\iadminstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\ihodstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iissuerstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\ireceiverstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iroleRepository;
use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\istoresrequisitionService;
use Illuminate\Support\Collection;

class _storesrequisitionService implements istoresrequisitionService
{
    protected $storesrequisitionrepo, $hodstoresrequisitionapprovalrepo, $userrepo, $issuerstoresrequisitionapprovalrepo, $receiverstoresrequisitionapprovalrepo, $adminstoresrequisitionapprovalrepo, $departmentrepo, $rolerepo;
    
    public function __construct(istoresrequisitionInterface $storesrequisitionrepo, ihodstoresrequisitionapprovalInterface $hodstoresrequisitionapprovalrepo, iuserInterface $userrepo, iissuerstoresrequisitionapprovalInterface $issuerstoresrequisitionapprovalrepo,
     ireceiverstoresrequisitionapprovalInterface $receiverstoresrequisitionapprovalrepo, iadminstoresrequisitionapprovalInterface $adminstoresrequisitionapprovalrepo, idepartmentInterface $departmentrepo,iroleRepository $rolerepo)
    {
        $this->storesrequisitionrepo = $storesrequisitionrepo;
        $this->hodstoresrequisitionapprovalrepo = $hodstoresrequisitionapprovalrepo;
        $this->userrepo = $userrepo;
        $this->issuerstoresrequisitionapprovalrepo = $issuerstoresrequisitionapprovalrepo;
        $this->adminstoresrequisitionapprovalrepo=$adminstoresrequisitionapprovalrepo;
        $this->receiverstoresrequisitionapprovalrepo = $receiverstoresrequisitionapprovalrepo;
        $this->departmentrepo = $departmentrepo;
        $this->rolerepo=$rolerepo;
    }

    public function getstoresrequisition($storesrequisitionuuid)
    {
        return $this->storesrequisitionrepo->getstoresrequisition($storesrequisitionuuid);
    }
    public function getmystoresrequisitions($userid,$status=null, $searchuuid=null)
    {
        return $this->storesrequisitionrepo->getmystoresrequisitions($userid,$status, $searchuuid);
    }
    public function gethodstoresrequestssubmissions()
    {
        return $this->storesrequisitionrepo->gethodstoresrequestssubmissions();
    }
    public function getadminissuersbyrole($role)
    {
        return $this->rolerepo->getusersbyrole($role);
    }
    public function getdeptmembersbydepartmentid($departmentid)
    {
        return $this->departmentrepo->getusers($departmentid);
    }
    public function getissuerrequisitionapprovalrecord($storesrequisitionuuid)
    {
        return $this->issuerstoresrequisitionapprovalrepo->getissuerrequisitionapproval($storesrequisitionuuid);
    }
    public function getadminrequisitionapprovalrecord($storesrequisitionuuid)
    {
        return $this->adminstoresrequisitionapprovalrepo->getadminrequisitionapproval($storesrequisitionuuid);
    }
    public function gethodrequisitionapprovalrecord($storesrequisitionuuid)
    {
        return $this->hodstoresrequisitionapprovalrepo->gethodrequisitionapproval($storesrequisitionuuid);
    }
    public function getreceiverrequisitionapprovalrecord($storesrequisitionuuid)
    {
        return $this->receiverstoresrequisitionapprovalrepo->getreceiverrequisitionapproval($storesrequisitionuuid);
    }
    public function getstoresrequisitionsawaitingapproval($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('P', $departmentid, $searchuuid);
    }
    // Get stores requisitions awaiting delivery implemented on tabs
    public function getstoresrequisitionsawaitingdelivery($departmentid=null,$searchuuid=null,$statussearch=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions(['A','O','V'], $departmentid, $searchuuid, $statussearch);
    }
    public function getawaitingissuingstoresrequisitions($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions(['A','O'], $departmentid, $searchuuid);
    }
    public function getapprovedstoresrequisitions($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('A', $departmentid, $searchuuid);
    }
    public function getopenedstoresrequisitions($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('O', $departmentid, $searchuuid);
    }
    public function getstoresrequisitionsawaitingclearance($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('V', $departmentid, $searchuuid);
    }
    public function getdeliveredstoresrequisitions($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('D', $departmentid, $searchuuid);
    }
    public function getrecievedstoresrequisitions($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('C', $departmentid, $searchuuid);
    }
    public function getrejectedstoresrequisitions($departmentid=null,$searchuuid=null)
    {
        return $this->storesrequisitionrepo->getdeptstoresrequisitions('R', $departmentid, $searchuuid);
    }
    public function getstoresrequisitionrequestitems($storesrequisitionuuid)
    {
        return json_decode($this->storesrequisitionrepo->getstoresrequisition($storesrequisitionuuid)->requisitionitems, true);
    }
    public function getuserdepartmentid($useremail)
    {
        return $this->userrepo->getuserbyemail($useremail)->department->department_id;
    }
    public function getuserdepartmentname($userdepartmentid)
    {
        return $this->departmentrepo->getdepartment($userdepartmentid)->name;
    }
    public function getrecordowner($userid)
    {
      return $this->userrepo->getuser($userid);
    }
    public function gethodidforuser($useremail)
    {
      return $this->userrepo->getuserbyemail($useremail)->department->reportto;
    }
    public function updatestoresrequisitionrecord($storesrequisitionuuid,$data)
    {
        return $this->storesrequisitionrepo->updatestoresrequisition($storesrequisitionuuid,$data);
    }
    public function updatehodrecord($storesrequisitionuuid,$data)
    {
        return $this->hodstoresrequisitionapprovalrepo->updatehodrequisitionapproval($storesrequisitionuuid,$data);
    }
    public function updatereceiverrecord($storesrequisitionuuid,$data)
    {
        return $this->receiverstoresrequisitionapprovalrepo->updatereceiverrequisitionapproval($storesrequisitionuuid,$data);
    }
    public function updateissuerrequisitionrecord($storesrequisitionuuid,$data)
    {
        return $this->issuerstoresrequisitionapprovalrepo->updateissuerrequisitionapproval($storesrequisitionuuid,$data);
    }
    public function updateadminrequisitionrecord($storesrequisitionuuid,$data)
    {
        return $this->adminstoresrequisitionapprovalrepo->updateadminrequisitionapproval($storesrequisitionuuid,$data);
    }
    public function createstoresrequisitionrecord($data)
    {
        return $this->storesrequisitionrepo->createstoresrequisition($data);
    }
    public function createhodrequisitionapprovalrecord($data)
    {
        return $this->hodstoresrequisitionapprovalrepo->createhodrequisitionapproval($data);
    }
    public function createreceiverrequisitionapprovalrecord($data)
    {
        return $this->receiverstoresrequisitionapprovalrepo->createreceiverrequisitionapproval($data);
    }
    public function createadminrequisitionapprovalrecord($data)
    {
        return $this->adminstoresrequisitionapprovalrepo->createadminrequisitionapproval($data);
    }
    public function exportdata($status)
    {
        return $this->storesrequisitionrepo->exportstoresrequisitionreport($status);
    }
}
 