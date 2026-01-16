<?php

namespace App\Interfaces\repositories;


interface ischoolmonthlyreturnInterface
{
    public function createmonthlyreturn($data);
    public function getmonthlyreturnbyid($id);
    public function getmonthlyreturns($status, $year=null, $month=null);
    public function getmonthlyreturnsbyschoolnumber($schoolnumber, $status, $year=null, $month=null, $perpage=null);
    public function getmonthlyreturnbyexpensecategory($categoryid);
    public function getmonthlyreturnbycurrency($currencyid);
    public function updatemonthlyreturn($id, $data);
    public function deletemonthlyreturn($id);
}
