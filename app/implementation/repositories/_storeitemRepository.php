<?php

namespace App\implementation\repositories;

use App\Models\Storeitem;

class _storeitemRepository
{
    protected $model;
    public function __construct(Storeitem $model)
    {
       $this->model=$model;
    }

    public function getstoreitems()
    {
        return $this->model->all();
    }
    public function getstoreitemsByUser($userid)
    {
        return $this->model->where('user_id', $userid)->first();
    }
    public function getstoreitem($id)
    {
        return $this->model->where('id', $id)->first();
    }
    public function createstoreitem($data)
    {
        try
        {
            $check = $this->model->where("itemdetail", $data['itemdetail'])->first();
            if ($check) {
                return ['status' => "error", 'message' => 'Store item already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Store item created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updatestoreitem($id, $data)
    {
        try
        {
            $check = $this->model->find($id);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store item not found'];
            }
            $check->update($data);
            return ['status' => "success", 'message' => 'Store item updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deletestoreitem($id)
    {
        try
        {
            $check = $this->model->find($id);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store item not found'];
            }
            $check->delete();
            return ['status' => "success", 'message' => 'Store item deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
}
