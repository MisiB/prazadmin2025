<?php

namespace App\Http\Controllers;

use App\Interfaces\services\iexchangerateService;
use Illuminate\Http\Request;

class ExchangerateController extends Controller
{
    protected $service;
    public function __construct(iexchangerateService $service)
    {
        $this->service = $service;
    } 
    
    public function getlatest($currency_id=null){
        return $this->service->getlatestexchangerate($currency_id);
    }
}
