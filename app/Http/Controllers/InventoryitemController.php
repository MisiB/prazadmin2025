<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\services\iinventoryitemService;

class InventoryitemController extends Controller
{
    protected $inventoryitemService;
    public function __construct(iinventoryitemService $inventoryitemService)
    {
        $this->inventoryitemService = $inventoryitemService;
    }
    public function getinventories(){
        return response()->json($this->inventoryitemService->getinventories(),200);
    }
}
