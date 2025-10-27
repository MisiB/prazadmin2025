<?php

namespace App\Interfaces\services;

interface itenderService
{
    public function createtender($data);

    public function updatetender($id, $data);

    public function changestatus($id, $status);
}
