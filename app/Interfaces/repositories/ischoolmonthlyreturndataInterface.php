<?php

namespace App\Interfaces\repositories;


interface ischoolmonthlyreturndataInterface
{
    public function createmonthlyreturndata($data);
    public function getmonthlyreturndatabyid($id);
    public function getmonthlyreturndatabyreturnid($monthlyreturnid, $perPage);
    public function getmonthlyreturndatabysourceoffund($sourceoffund);
    public function getmonthlyreturndatabycurrencyid($currencyid);
    public function updatemonthlyreturndata($id, $data);
    public function deletemonthlyreturndata($id);
}