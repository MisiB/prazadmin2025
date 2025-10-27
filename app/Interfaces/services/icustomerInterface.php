<?php

namespace App\Interfaces\services;

interface icustomerInterface
{
    public function getall();
    public function getcustomerbyregnumber($regnumber);
    public function createcustomer($data);
    public function verifycustomer($data);
    public function updatecustomer($data);
    public function deletecustomer($id);
    public function searchcustomer($needle);
}
