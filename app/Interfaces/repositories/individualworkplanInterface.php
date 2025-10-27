<?php

namespace App\Interfaces\repositories;

interface individualworkplanInterface
{
    public function getindividualworkplans($user_id, $strategy_id, $year);

    public function getsubordinatesworkplans($approver_id, $strategy_id, $year);

    public function createindividualworkplan($data);

    public function updateindividualworkplan($id, $data);

    public function deleteindividualworkplan($id);

    public function getindividualworkplan($id);

    public function approveindividualworkplan($id, $remarks = null);
}
