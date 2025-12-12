<?php

namespace App\Interfaces\repositories;

interface irecurringTaskInterface
{
    public function getmyrecurringtasks($userId);

    public function getrecurringtask($id);

    public function createrecurringtask(array $data);

    public function updaterecurringtask($id, array $data);

    public function deleterecurringtask($id);

    public function getactiverecurringtasks();

    public function updatelastcreateddate($id, $date);
}
