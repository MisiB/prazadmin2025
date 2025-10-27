<?php

namespace App\Http\Controllers;

use App\Interfaces\icurrencyInterface;
use Illuminate\Http\Request;

class currencyController extends Controller
{
    protected $repo;
    public function __construct(icurrencyInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $currencies = $this->repo->getcurrencies()->where('status', 'active')->get();
        return $currencies;
    }

 
}
