<?php

namespace App\implementation\services;

use App\Interfaces\repositories\iexchangerateInterface;
use App\Interfaces\services\iexchangerateService;

class _exchangerateService implements iexchangerateService
{
    /**
     * Create a new class instance.
     */
    protected $repository;
    public function __construct(iexchangerateInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getexchangerates()
    {
        return $this->repository->getexchangerates();
    }
    public function getexchangerate($id)
    {
        return $this->repository->getexchangerate($id);
    }
    public function getexchangeratebycurrency($currency_id)
    {
        return $this->repository->getexchangeratebycurrency($currency_id);
    }
    public function getexchangeratesbyprimarycurrency($currency_id)
    {
        return $this->repository->getexchangeratesbyprimarycurrency($currency_id);
    }
    public function getlatestexchangerate($currency_id=null)
    {
        $data= $this->repository->getlatestexchangerate($currency_id);
        if($data != null){
            return  [
                "id"=>$data->id,
                "primarycurrency"=>$data->primarycurrency->name,
                "secondarycurrency"=>$data->secondarycurrency->name,
               "value"=>$data->value];
        }
        return [];
    }
    public function createexchangerate($data)
    {
        return $this->repository->createexchangerate($data);
    }
    public function updateexchangerate($id, $data)
    {
        return $this->repository->updateexchangerate($id, $data);
    }
    public function deleteexchangerate($id)
    {
        return $this->repository->deleteexchangerate($id);
    }
}
