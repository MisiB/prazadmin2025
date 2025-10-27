<?php

namespace App\Interfaces\repositories;

interface idocumentrequirementInterface
{
     public function getall($search);
     public function getbyid($id);
     public function create($data);
     public function update($id, $data);
     public function delete($id);
}
