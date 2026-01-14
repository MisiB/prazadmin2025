<?php

namespace App\Interfaces\repositories;

use Illuminate\Support\Collection;

interface ischoolmonthlyreturnInterface
{
    public function createmonthlyreturn($data);
    public function getmonthlyreturnbyid($id);
    public function getmonthlyreturns($status, $year=null, $month=null):Collection;
    public function getmonthlyreturnsbyschoolnumber($schoolnumber, $status, $year=null, $month=null):Collection;
    public function getmonthlyreturnbyexpensecategory($categoryid);
    public function getmonthlyreturnbycurrency($ccurrencyid);
    public function updatemonthlyreturn($id, $data);
    public function deletemonthlyreturn($id);
}
