<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnlinepaymentRequest;
use App\Interfaces\services\ionlinepaymentService;

class OnlinepaymentController extends Controller
{
    protected $service;

    public function __construct(ionlinepaymentService $service)
    {
        $this->service = $service;
    }

    public function initiatePayment(OnlinepaymentRequest $request)
    {

        return $this->service->initiatepayment($request->all());
    }

    public function checkPayment($uuid)
    {
        return $this->service->checkpaymentstatus($uuid);
    }

    public function getepayment($uuid)
    {
        return $this->service->getepayment($uuid);
    }
}
