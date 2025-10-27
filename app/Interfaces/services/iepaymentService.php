<?php

namespace App\Interfaces\services;

interface iepaymentService
{
    public function checkinvoice($data);

    public function posttransaction($data);
}
