<?php

namespace App\Interfaces\repositories;

interface iaccountsettingInterface
{
    public function getsettings();
    public function create(array $data);
}
