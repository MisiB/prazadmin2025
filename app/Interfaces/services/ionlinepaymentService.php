<?php

namespace App\Interfaces\services;

interface ionlinepaymentService
{
    public function initiatepayment($data);

    public function checkpaymentstatus($uuid);

    public function getepayment($uuid);
}
