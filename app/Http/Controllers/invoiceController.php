<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoiceRequest;
use App\Interfaces\services\iinvoiceService;
use App\Http\Requests\InvoiceSettlementRequest;
use App\Interfaces\services\isuspenseService;

class invoiceController extends Controller
{
    protected $service;
    protected $suspenseService;
    public function __construct(iinvoiceService $service,isuspenseService $suspenseService)
    {
        $this->service = $service;
        $this->suspenseService = $suspenseService;
    }

    public function show($invoice_number){
        return $this->service->getinvoice($invoice_number);
    }

    public function store(CreateInvoiceRequest $request){
        return $this->service->createinvoice([
            'invoicenumber'=>$request->invoicenumber,
            'regnumber'=>$request->regnumber,
            'itemcode'=>$request->inventoryItem,
            'currency'=>$request->currency,
            'invoicesource'=>'egp',
            'amount'=>$request->amount,
        ]);
    }
    public function destroy($invoice_number){
        return $this->service->deleteinvoice($invoice_number);
    }

    public function settleinvoice(InvoiceSettlementRequest $request){       
     
        return $this->suspenseService->suspenseutilization(["regnumber"=>$request['regnumber'],"invoice_number"=>$request->invoicenumber,"accounttype"=>$request->type,"receiptnumber"=>$request->receiptnumber]);
    }
}
