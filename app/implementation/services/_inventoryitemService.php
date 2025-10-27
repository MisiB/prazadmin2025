<?php

namespace App\implementation\services;

use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\services\iinventoryitemService;

class _inventoryitemService implements iinventoryitemService
{
    /**
     * Create a new class instance.
     */
    protected $inventoryitemrepo;
    public function __construct(iinventoryitemInterface $inventoryitemrepo)
    {
        $this->inventoryitemrepo = $inventoryitemrepo;
    }
    public function getinventories(){
        return $this->inventoryitemrepo->getinventories();
    }
    public function getInventoryItemByItemcode($itemcode){
        return $this->inventoryitemrepo->getInventoryItemByItemcode($itemcode);
    }
    public function getinventory($id){
        return $this->inventoryitemrepo->getinventory($id);
    }
    public function createinventory($data){
        return $this->inventoryitemrepo->createinventory($data);
    }
    public function updateinventory($id, $data){
        return $this->inventoryitemrepo->updateinventory($id, $data);
    }
    public function deleteinventory($id){
        return $this->inventoryitemrepo->deleteinventory($id);
    }
}
