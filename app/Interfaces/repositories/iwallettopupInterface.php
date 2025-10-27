<?php

namespace App\Interfaces\repositories;

interface iwallettopupInterface
{
    
    public function getwallettopups($year);
    public function getwallettopup($id);
    public function getwallettopupbycustomer($customer_id);
    public function createwallettopup($data);
    public function updatewallettopup($id, $data);
    public function deletewallettopup($id);
    public function makedecision($id,$data);
    public function linkwallet($data);
}
