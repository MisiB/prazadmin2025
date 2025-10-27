<?php

namespace App\Interfaces\services;

interface ihttpInterface
{
    public function getaccounttypes();
    public function getaccounttype($id);
    public function createaccounttype(array $data);
    public function updateaccounttype($id, array $data);
    public function deleteaccounttype($id);

    public function getmodules();
    public function getmodule($id);
    public function createmodule(array $data);
    public function updatemodule($id, array $data);
    public function deletemodule($id);

    public function getsubmodules();
    public function getsubmodule($id);
    public function createsubmodule(array $data);
    public function updatesubmodule($id, array $data);
    public function deletesubmodule($id);

    public function getpermissions();
    public function getpermission($id);
    public function createpermission(array $data);
    public function updatepermission($id, array $data);
    public function deletepermission($id);

    public function updateProfile(array $data);
    public function updatePassword(array $data);

    public function getaccountsettings();
    public function createaccountsetting(array $data);
}
