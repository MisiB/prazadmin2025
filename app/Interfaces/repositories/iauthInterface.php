<?php

namespace App\Interfaces\repositories;

interface iauthInterface
{
     public function Login(array $credentials);
    // public function Loginfromportal(array $credentials);
     public function Logout();
     public function register(array $credentials);
    // public function registerfromportal(array $credentials);
     public function forgotpassword(array $credentials);
     public function resetpassword(array $credentials);
     public function getprofile();
     public function updateprofile(array $user);
     public function updatepassword(array $user);
     public function updateapprovalcode(array $data);
     public function checkapprovalcode($approvalcode);
}
