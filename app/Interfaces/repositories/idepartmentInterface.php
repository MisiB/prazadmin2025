<?php

namespace App\Interfaces\repositories;

interface idepartmentInterface
{
     public function getdepartments();
     public function getdepartment($id);
     public function create($department);
     public function update($id,$department);
     public function delete($id);

     public function getusers($id);
     public function getuser($id);
     public function getsupervisor($user_id);
     public function getmysubordinates();
     public function createuser($data);
     public function updateuser($id,$data);
     public function deleteuser($id);
}
