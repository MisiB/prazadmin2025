<?php

namespace App\Http\Controllers;

use App\Interfaces\services\ipaynowInterface;
use Illuminate\Http\Request;

class PaynowController extends Controller
{
    protected $paynowService;

    public function __construct(ipaynowInterface $paynowService)
    {
        $this->paynowService = $paynowService;
    }

    public function check($uuid, Request $request)
    {

        return $this->paynowService->checkpaymentstatus($request->all());
    }
}
