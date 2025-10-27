<?php

namespace App\Interfaces\services;

interface istoresrequisitionService
{
    public function getstoresrequisition($storesrequisitionuuid);

    public function getmystoresrequisitions($userid,$status=null, $searchuuid=null);

    public function gethodstoresrequestssubmissions();

    public function getadminissuersbyrole($role);

    public function getdeptmembersbydepartmentid($departmentid);

    public function getissuerrequisitionapprovalrecord($storesrequisitionuuid);

    public function getadminrequisitionapprovalrecord($storesrequisitionuuid);

    public function gethodrequisitionapprovalrecord($storesrequisitionuuid);

    public function getreceiverrequisitionapprovalrecord($storesrequisitionuuid);

    public function getstoresrequisitionsawaitingapproval($departmentid=null,$searchuuid=null);

    public function getstoresrequisitionsawaitingdelivery($departmentid=null,$searchuuid=null, $statussearch=null);

    public function getawaitingissuingstoresrequisitions($departmentid=null,$searchuuid=null);

    public function getapprovedstoresrequisitions($departmentid=null,$searchuuid=null);

    public function getopenedstoresrequisitions($departmentid=null,$searchuuid=null);

    public function getstoresrequisitionsawaitingclearance($departmentid=null,$searchuuid=null);

    public function getdeliveredstoresrequisitions($departmentid=null,$searchuuid=null);

    public function getrecievedstoresrequisitions($departmentid=null,$searchuuid=null);

    public function getrejectedstoresrequisitions($departmentid=null,$searchuuid=null);

    public function getstoresrequisitionrequestitems($storesrequisitionuuid);

    public function getuserdepartmentid($useremail);

    public function getuserdepartmentname($userdepartmentid);

    public function getrecordowner($userid);

    public function gethodidforuser($useremail);

    public function updatestoresrequisitionrecord($storesrequisitionuuid,$data);

    public function updatehodrecord($storesrequisitionuuid,$data);

    public function updatereceiverrecord($storesrequisitionuuid,$data);

    public function updateissuerrequisitionrecord($storesrequisitionuuid,$data);

    public function updateadminrequisitionrecord($storesrequisitionuuid,$data);

    public function createstoresrequisitionrecord($data);

    public function createhodrequisitionapprovalrecord($data);

    public function createreceiverrequisitionapprovalrecord($data);

    public function createadminrequisitionapprovalrecord($data);

    public function exportdata($status);
}
 