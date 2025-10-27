<?php

namespace App\Interfaces\services;

interface ipayeeService
{
    public function getbyemail($email);

    public function getbyuuid($uuid);

    public function create(array $details);

    public function update(array $details, $uuid);

    public function retrievepayments($email);
}
