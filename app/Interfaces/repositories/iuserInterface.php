<?php

namespace App\Interfaces\repositories;

interface iuserInterface
{
    public function getusers($search = null);
    public function getall();
    public function getuser($id);
    public function getuserbyemail($email);
    public function createuser(array $user, array $roles);
    public function updateuser($id, array $user, array $roles);
    public function deleteuser($id);
    public function assignroles($id, array $roles);
    public function removeroles(int $id, array $roles);
    public function assignaccounttype(int $id, array $accounttype);
    public function removeaccounttype(int $id, array $accounttype);
    public function resetpassword(int $id);
    public function updatepassword(int $id, array $user);
}
