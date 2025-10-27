<?php

namespace App\Interfaces\repositories;

interface ipurchaseerequisitionInterface
{
    
    public function getpurchaseerequisitions($year);
    public function getpurchaseerequisition($id);
    public function getpurchaseerequisitionbyuuid($uuid);
    public function getpurchaseerequisitionbydepartment($year,$department_id);
    public function createpurchaseerequisition($data);
    public function updatepurchaseerequisition($id,$data);
    public function deletepurchaseerequisition($id);
    public function getpurchaseerequisitionbystatus($year,$status);
    public function getpurchaserequeisitionbyworkflowparameter($year);
    public function makedecision($id,$data); 
    public function recommend($id,$data);

    public function createaward($data);
    public function updateaward($id,$data);
    public function deleteaward($id);
    public function getawards($year);
    public function getaward($id);
    public function approveaward($id);

    public function createawarddocument($data);
    public function updateawarddocument($id,$data);
    public function deleteawarddocument($id);
    public function getawarddocuments($id);
}
