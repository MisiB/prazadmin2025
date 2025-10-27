<?php

namespace App\Interfaces\repositories;

interface istrategyInterface
{
    public function getstrategies();
    public function getstrategy($id);
    public function getstrategybyuuid($uuid,$year);
    public function createstrategy(array $data);
    public function updatestrategy($id,array $data);
    public function deletestrategy($id);
    public function copy($id,$data);
    public function getstrategybydepartment($strategy_id,$department_id,$year);
    public function  gettargetmatrixbystrategy($strategy_id,$department_id,$year);
    
}
