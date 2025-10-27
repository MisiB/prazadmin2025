<?php

namespace App\Interfaces\repositories;

use Spatie\Permission\Contracts\Role;

interface iroleRepository
{
    public function getroles();
    public function getrole(int $id):?Role;
     public function getusersbyrole($rolename);
    public function createrole(array $role);
    public function updaterole(int $id, array $role);
    public function deleterole(int $id);
    public  function assignpermissions(int $id, array $permissions);
}
