<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetWalletByTypeRequest;
use App\Interfaces\services\isuspenseService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected $suspenseService;
    public function __construct(isuspenseService $suspenseService)
    {
        $this->suspenseService = $suspenseService;
    }

    public function getwallet($regnumber){
        return $this->suspenseService->getsuspensewallet($regnumber);
    }
    public function getwalletbalance(GetWalletByTypeRequest $request){
        return $this->suspenseService->getwalletbalance($request->regnumber,$request->type,$request->currency);
    }
 
}
