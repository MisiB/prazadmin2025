<?php

namespace App\Interfaces\repositories;

interface ipaymentrequisitionInterface
{
    public function getpaymentrequisitions($year);
    public function getpaymentrequisitionbydepartment($year,$department_id);
    public function getpaymentrequisition($id);
    public function getpaymentrequisitionbysource($source,$source_id);
    public function createpaymentrequisition($data);
    public function updatepaymentrequisition($id, $data);
    public function deletepaymentrequisition($id);
    public function paymentrequisitionrecommend($id);
    public function createdocument($data);
    public function updatedocument($id, $data);
    public function deletedocument($id);
    public function getpaymentrequisitiondocuments($id);
}
