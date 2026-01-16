<?php

namespace App\Interfaces\repositories;


interface ischoolexpensecategoryInterface
{
    public function createexpensecategory($data);
    public function getexpensecategories();
    public function getexpensecategorybyid($id);
    public function getexpensecategorybyname($name);
    public function updateexpensecategory($id, $data);
    public function deleteexpensecategory($id);
} 
