<?php

namespace App\Interfaces\repositories;

interface ibudgetInterface
{
    public function getbudgets();
    public function getbudget($id);
    public function getbudgetbyuuid($uuid);
    public function createbudget($data);
    public function updatebudget($id, $data);
    public function deletebudget($id);
    public function approvebudget($id);

    public function getbudgetitems($budget_id);
    public function getbudgetitembyuuid($uuid);
    public function getbudgetitemsbydepartment($budget_id,$department_id);
    public function createbudgetitem(array $data);
    public function updatebudgetitem($id, array $data);
    public function deletebudgetitem($id);
    public function getbudgetitem($id);
    public function approvebudgetitem($id);

    public function getbudgetvirements($budget_id);
    public function createbudgetvirement(array $data);
    public function updatebudgetvirement($id, array $data);
    public function deletebudgetvirement($id);
    public function getbudgetvirement($id);
    public function approvebudgetvirement($id);
    public function rejectbudgetvirement(array $data);

   
}
