<?php

namespace App\Interfaces\repositories;

interface invoiceInterface
{
    public function getInvoices($fromDate, $toDate,$status,array $inventoryItems,array $currencyItems);
    public function getInvoicespaginated($fromDate, $toDate,$status,array $inventoryItems,array $currencyItems);
    public function getcomparisonreport($firstfromDate, $firsttoDate,$secondfromDate, $secondtoDate,$status,array $inventoryItems,array $currencyItems);
    public function getInvoiceDetails($invoiceId);
    public function getInvoicebyCustomer($customerId);
    public function getquarterlyreport($year,$status,array $inventoryItems,array $currencyItems);
    public function getInvoiceByInvoiceNumber($invoiceNumber);
    public function markInvoiceAsPaid($invoiceNumber);
    public function createInvoice($data);
    public function updateInvoice($data);
    public function deleteInvoice($invoicenumber);
    public function settleInvoice($invoicenumber,$receiptnumber=null);

    public function settleworkshopinvoice($invoicenumber);
}
   
