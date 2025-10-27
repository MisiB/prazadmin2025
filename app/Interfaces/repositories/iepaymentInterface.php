<?php

namespace App\Interfaces\repositories;

interface iepaymentInterface
{
    public function getepayments($customer_id);

    public function createepayment($data);

    public function updateepayment($id, $data);

    public function getepaymentbyinitiationid($initiationid);
}
