<?php

namespace App\Interfaces\repositories;


interface ischoolInterface
{
    public function createschool($data);
    public function getschools();
    public function getschoolbyid($id);
    public function getschoolbynumber($schoolnumber);
    public function getschoolbynameornumber($schoolname=null, $schoolnumber=null);
    public function updateschool($schoolnumber, $data);
    public function deleteschool($schoolnumber);
}
