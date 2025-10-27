<?php

namespace App\Interfaces\repositories;

interface iinventoryitemInterface
{
    public function getinventories();
    public function getInventoryItemByItemcode($itemcode);
    public function getinventory($id);
    public function createinventory($data);
    public function updateinventory($id, $data);
    public function deleteinventory($id);
}
