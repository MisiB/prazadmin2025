<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iinventoryitemInterface;
use App\Models\Inventoryitem;

class _inventoryitemRepository implements iinventoryitemInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    public function __construct(Inventoryitem $model)
    {
        $this->model = $model;
    }

    public function getinventories(){
        return $this->model->all();
    }
    public function getInventoryItemByItemcode($itemcode){
        return $this->model->where('name', $itemcode)->first();
    }

    public function getinventory($id){
        return $this->model->find($id);
    }

    public function createinventory($data){
        try {
        $exist = $this->model->where('name', $data['name'])->first();
        if($exist){
            return ['status' => "error", 'message' => 'Inventory already exists'];
        }
        $this->model->create($data);
        return ['status' => "success", 'message' => 'Inventory created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function updateinventory($id, $data){
            try {
            $exist = $this->model->where('name', $data['name'])->first();
            if($exist && $exist->id != $id){
                return ['status' => "error", 'message' => 'Inventory already exists'];
            }
            $this->model->find($id)->update($data);
            return ['status' => "success", 'message' => 'Inventory updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function deleteinventory($id){
        try {
            $this->model->find($id)->delete();
            return ['status' => "success", 'message' => 'Inventory deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
}
