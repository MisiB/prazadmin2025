<?php

namespace App\Interfaces\repositories;

use Illuminate\Support\Collection;

interface ischoolInterface
{
    public function createschool($data);
    public function getschools():Collection;
    public function getschoolbyid($id);
    public function getschoolbynumber($schoolnumber);
    public function getschoolbynameornumber($schoolname=null, $schoolnumber=null);
    public function updateschool($schoolnumber, $data);
    public function deleteschool($schoolnumber);
}
