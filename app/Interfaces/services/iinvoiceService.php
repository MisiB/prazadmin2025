<?php

namespace App\Interfaces\services;

interface iinvoiceService
{
    public function getinvoice($invoice_number);
    public function createinvoice($data);
    public function deleteinvoice($invoice_number);
}
