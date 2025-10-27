<?php

namespace App\Interfaces\repositories;

interface iworkplanInterface
{
    
    public function getworkplans($strategy_id,$year);
    public function createworkplan($data);
    public function updateworkplan($id,$data);
    public function deleteworkplan($id);

    public function getworkplan($id);
    public function getworkplabreakdownbyuser($user_id,$year);
    public function getworkplanbreakdown($id);
    public function getworkplanbreakdownlist($id);
    public function createworkplanbreakdown($data);
    public function updateworkplanbreakdown($id,$data);
    public function deleteworkplanbreakdown($id);
    public function getworkplanassignees($id);
    public function getworkplanassignee($id);
    public function createworkplanassignee($data);
    public function updateworkplanassignee($id,$data);
    public function deleteworkplanassignee($id);
}
