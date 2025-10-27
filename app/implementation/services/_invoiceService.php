<?php

namespace App\implementation\services;

use App\Interfaces\repositories\invoiceInterface;
use App\Interfaces\services\iinvoiceService;

class _invoiceService implements iinvoiceService
{
    /**
     * Create a new class instance.
     */
    protected $repo;

    public function __construct(invoiceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getinvoice($invoice_number)
    {
        $invoice = $this->repo->getInvoiceByInvoiceNumber($invoice_number);
        if (! $invoice) {
            return ['status' => 'ERROR', 'message' => 'Invoice not found', 'data' => null];
        } elseif ($invoice->status == 'PAID') {
            return ['status' => 'ERROR', 'message' => 'Invoice already paid', 'data' => null];
        } else {
            return [
                'status' => 'SUCCESS',
                'message' => 'success',
                'data' => [
                    'id' => $invoice->id,
                    'regnumber' => $invoice->customer->regnumber ?? null,
                    'organisation' => $invoice->customer->name ?? null,
                    'inventoryItem' => $invoice->inventoryitem->name ?? null,
                    'invoicenumber' => $invoice->invoicenumber ?? null,
                    'currency' => $invoice->currency->name ?? null,
                    'amount' => $invoice->amount ?? null,
                    'status' => $invoice->status,
                ]];
        }

    }

    public function createinvoice($data)
    {
        return $this->repo->createInvoice($data);
    }

    public function deleteinvoice($invoice_number)
    {
        return $this->repo->deleteinvoice($invoice_number);
    }
}
