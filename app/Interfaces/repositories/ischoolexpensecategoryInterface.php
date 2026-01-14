<?php

namespace App\Interfaces\repositories;

use Illuminate\Support\Collection;

interface ischoolexpensecategoryInterface
{
    public function createexpensecategory($data);
    public function getexpensecategories():Collection;
    public function getexpensecategorybyid($id);
    public function getexpensecategorybyname($name);
    public function updateexpensecategory($id, $data);
    public function deleteexpensecategory($id);
} 
