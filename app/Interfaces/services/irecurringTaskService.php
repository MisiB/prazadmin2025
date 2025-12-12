<?php

namespace App\Interfaces\services;

interface irecurringTaskService
{
    public function getmyrecurringtasks($userId);

    public function getrecurringtask($id);

    public function createrecurringtask(array $data);

    public function updaterecurringtask($id, array $data);

    public function deleterecurringtask($id);

    public function processrecurringtasks();
}
