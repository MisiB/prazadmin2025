<?php

namespace App\Interfaces\repositories;

interface isuspenseInterface
{
    public function getpendingsuspensewallets();

    public function getpendingsuspense($regnumber, $accounttype, $currency);

    public function create(array $data);

    public function createmonthlysuspensewallets($month, $year);

    public function getmonthlysuspensewallets($month, $year);

    public function getsuspensewallet($regnumber);

    public function getsuspense($id);

    public function getsuspensestatement($customer_id);

    public function checksuspsnse($source_id, $sourcetype);

    public function getwalletbalance($regnumber, $accounttype, $currency);

    public function deductwallet($regnumber, $invoice_id, $accounttype, $currency, $amount, $receiptnumber);

    public function createSuspenseutilization($suspense_id, $invoice_id, $amount, $receiptnumber);
}
