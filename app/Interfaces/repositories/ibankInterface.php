<?php

namespace App\Interfaces\repositories;

interface ibankInterface
{
    public function getBanks();

    public function getBank($id);

    public function getBankbytoken($token);

    public function getBankBySalt($salt);

    public function createBank($data);

    public function updateBank($id, $data);

    public function deleteBank($id);
}
