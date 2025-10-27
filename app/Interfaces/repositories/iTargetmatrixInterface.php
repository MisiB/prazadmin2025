<?php

namespace App\Interfaces\repositories;

interface iTargetmatrixInterface
{
    public function gettargetmatrices($target_id);
    public function gettargetmatrix($id);
    public function createtargetmatrix(array $data);
    public function updatetargetmatrix($id, array $data);
    public function deletetargetmatrix($id);

    
}

