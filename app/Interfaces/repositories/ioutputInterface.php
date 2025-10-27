<?php

namespace App\Interfaces\repositories;

interface ioutputInterface
{
    public function getoutputs($outcome_id);
    public function getoutput($id);
    public function createoutput(array $data);
    public function updateoutput($id,array $data);
    public function deleteoutput($id);
    public function approveoutput($id);
    public function unapproveoutput($id);
    public function adddepartmentoutput(array $data);
    public function updatedepartmentoutput($id,array $data);
    public function deletedepartmentoutput($id);
    public function getdepartmentoutput($id);

 
}
