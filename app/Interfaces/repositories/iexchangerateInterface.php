<?php

namespace App\Interfaces\repositories;

interface iexchangerateInterface
{
    public function getexchangerates();
    public function getexchangerate($id);
    public function getexchangeratebycurrency($currency_id);
    public function getexchangeratesbyprimarycurrency($currency_id);
    public function getlatestexchangerate($currency_id=null);
    public function createexchangerate($data);
    public function updateexchangerate($id, $data);
    public function deleteexchangerate($id);
  
}