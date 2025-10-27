<?php

namespace App\Interfaces\repositories;

interface ipayeeInterface
{
    public function getbyemail($email);

    public function getbyuuid($uuid);

    public function create(array $details);

    public function update(array $details, $uuid);
}
