<?php

namespace App\Interfaces\repositories;

use Illuminate\Support\Collection;

interface icurrencyInterface
{
    public function getcurrencies();
    public function getcurrency($id);
    public function getCurrencyByCode($code);
    public function getcurrenciesbystatus($status):Collection;
    public function createcurrency($data);
    public function updatecurrency($id, $data);
    public function deletecurrency($id);
}
