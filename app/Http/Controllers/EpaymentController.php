<?php

namespace App\Http\Controllers;

use App\Http\Requests\EpaymentRequest;
use App\Interfaces\services\iepaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EpaymentController extends Controller
{
    protected $service;

    public function __construct(iepaymentService $service)
    {
        $this->service = $service;
    }

    public function checkinvoice($invoicenumber, Request $request)
    {
        $token = $request->header('AUTHORIZATION');
        $removedtoken = str_replace('"', ' ', $token);
        $newtoken = str_replace('Bearer ', ' ', $removedtoken);
        $final = Str::trim($newtoken);

        return $this->service->checkinvoice(['token' => $final, 'invoicenumber' => $invoicenumber]);
    }

    public function posttransaction(EpaymentRequest $request)
    {
        return $this->service->posttransaction(['initiationId' => $request['initiationId'],
            'TransactionDate' => $request['TransactionDate'],
            'Reference' => $request['Reference'],
            'Amount' => $request['Amount'],
            'Currency' => $request['Currency'],
            'status' => $request['status']]);
    }
}
