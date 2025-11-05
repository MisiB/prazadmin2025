<?php

namespace App\implementation\services;

use App\Interfaces\repositories\iworkshopInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iexchangerateInterface;
use App\Models\Exchangerate;
use App\Interfaces\services\iworkshopService;

class _workshopService implements iworkshopService
{
    protected $workshopRepo;
    public function __construct(iworkshopInterface $workshopRepo)
    {
        $this->workshopRepo = $workshopRepo;
    }
    public function getallworkshops($search=null)
    {
        return $this->workshopRepo->getallworkshops($search);
    }
    public function getworkshopbyid($id)
    {
        return $this->workshopRepo->getworkshopbyid($id);
    }
    public function updateworkshop($id,$data)
    {
        return $this->workshopRepo->updateworkshop($id,$data);
    }
    public function createworkshop($data)
    {
        return $this->workshopRepo->createworkshop($data);
    }
    public function deleteworkshop($id)
    {
        return $this->workshopRepo->deleteworkshop($id);
    }
    public function getopenworkshops()
    {
        return $this->workshopRepo->getopenworkshops();
    }
    public function viewworkshop($id)
    {
        return $this->workshopRepo->viewworkshop($id);
    }
    public function getworkshopinvoices($workshop_id,$status=null,$currency_id=null)
    {
        return $this->workshopRepo->getworkshopinvoices($workshop_id,$status,$currency_id);
    }
    public function getworkshopinvoicebyid($id)
    {
        return $this->workshopRepo->getworkshopinvoicebyid($id);
    }
    public function getordersbyregnumber($regnumber)
    {
        return $this->workshopRepo->getordersbyregnumber($regnumber);
    }
    public function getstatuslist()
    {
        return [['id'=>'DRAFT','name'=>'Draft'],['id'=>'PUBLISHED','name'=>'Published'],['id'=>'CANCELLED','name'=>'Cancelled']];
    }
    public function gettargetlist()
    {
        return [['id'=>'PE','name'=>'PE'],['id'=>'BIDDERS','name'=>'BIDDERS'],['id'=>'ALL','name'=>'ALL']];
    }
    public function createorder($data)
    {
        return $this->workshopRepo->createorder($data);
    }
    public function updateorder($id,$data)
    {
        return $this->workshopRepo->updateorder($id,$data);
    }
    public function deleteorder($id)
    {
        return $this->workshopRepo->deleteorder($id);
    }
    public function getorder($id)
    {
        return $this->workshopRepo->getorder($id);
    }
    public function getorders($workshop_id)
    {
        return $this->workshopRepo->getorders($workshop_id);
    }
    public function saveorderdocument($order_id,$data)
    {
        return $this->workshopRepo->saveorderdocument($order_id,$data);
    }
}
