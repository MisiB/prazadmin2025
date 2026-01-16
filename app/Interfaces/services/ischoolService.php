<?php

namespace App\Interfaces\services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ischoolService
{
    /**
     * Gets expenditure totals for a specified school
     */
    public function gettotalexpenditure($schoolnumber, $status, $year=null, $month=null); 
    public function getmonthlist();
    public function getheaders();
    public function monthlyreturnheaders();
    public function exportexcelreport($data, $schoolname);
    public function getmonthlyreturns($schoolnumber, $status, $year=null, $month=null, $perpage=null);
    public function getschoolexpensecategories();
    public function getsourceoffunds();
    public function getcurrenciesbystatus($status);
    public function getmonthlyreturnbyid($monthlyreturnid);
    public function getmonthlyreturndatabyreturnid($monthlyreturnid, $perpage=null);
    public function getschoolbynameornumber($schoolname=null, $schoolnumber=null);
    public function searchschool($schoolname=null, $schoolid=null);   
} 
  