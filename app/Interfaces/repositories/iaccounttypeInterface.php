<?php

namespace App\Interfaces\repositories;

interface iaccounttypeInterface
{
    public function getaccounttypes();
    public function getaccounttype(int $id);
    public function createaccounttype(array $accounttype);
    public function updateaccounttype(int $id, array $accounttype);
    public function deleteaccounttype(int $id);
}
