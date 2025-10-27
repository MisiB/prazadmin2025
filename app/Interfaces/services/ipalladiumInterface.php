<?php

namespace App\Interfaces\services;

use App\Models\Invoice;

interface ipalladiumInterface
{
     public function retrieve_customer($prnumber);
     public function get_gl_account($currency,$inventoryitemtype);
     public function post_invoice(Invoice $invoice);
     public function create_customer_account(array $array);
     public function create_supplier_account(array $array);
}
