<?php

namespace App\Interfaces\repositories;

interface icustomerInterface
{
    public function getall();
    public function getCustomerByRegnumber($regnumber);
    public function getCustomerById($id);
    public function  retrieve_last_regnumber($type);
    public function search($needle);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id); 
    public function searchname($name,$type);
    public function normalizename($name);
}
