<?php

namespace App\Interfaces\repositories;

interface iworkflowInterface
{
    public function getworkflows();
    public function getworkflow($id);
    public function getworkflowbystatus($status);
    public function createworkflow($data);
    public function updateworkflow($id,$data);
    public function deleteworkflow($id);
    public function getworkflowparameters($id);
    public function getworkflowparameter($id);
    public function createworkflowparameter($data);
    public function updateworkflowparameter($id,$data);
    public function deleteworkflowparameter($id);
}
