<?php

namespace App\Interfaces\repositories;

interface icurrencyInterface
{
    public function getcurrencies();
    public function getcurrency($id);
    public function getCurrencyByCode($code);
    public function createcurrency($data);
    public function updatecurrency($id, $data);
    public function deletecurrency($id);
}
