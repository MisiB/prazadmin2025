<?php

namespace App\Interfaces\repositories;

interface ipermissionInterface
{
    public function getpermissions();
    public function getpermission(int $id);
    public function createpermission(array $permission);
    public function updatepermission(int $id, array $permission);
    public function deletepermission(int $id);
}
