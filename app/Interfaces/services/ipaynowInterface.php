<?php

namespace App\Interfaces\services;

interface ipaynowInterface
{
    public function initiatepayment($data);
    public function checkpaymentstatus($data);
}
