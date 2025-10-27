<?php

namespace App\Interfaces\services;

interface ibanktransactionInterface
{
    public function createtransaction($data);
    public function recalltransaction($data);
    public function searchtransaction($data);
    public function claim($data);
}
