<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\services\iworkshopService;
class WorkshopController extends Controller
{
    protected $workshopService;

    public function __construct(iworkshopService $workshopService)
    {
        $this->workshopService = $workshopService;
    }

    public function getOpenWorkshops()
    {
        return $this->workshopService->getOpenWorkshops();
    }
    public function viewworkshop($id)
    {
        return $this->workshopService->viewworkshop($id);
    }
    public function getorders($regnumber)
    {
        return $this->workshopService->getordersbyregnumber($regnumber);
    }
}
