<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ileavetypeInterface;
use App\Models\Leavetype;

class _leavetypeRepository implements ileavetypeInterface
{
    protected $model;
    public function __construct(Leavetype $model)
    {
        $this->model = $model;
    }
    public function getleavetypes()
    {
        return $this->model->all();
    }
    public function getLeavetypeByName($name)
    {
        return $this->model->where('name', $name)->first();
    }
    public function getleavetype($id)
    {
        return $this->model->find($id);
    }

    public function createleavetype($data)
    {
        try
        {
            $check = $this->model->where("name", $data['name'])->first();
            if ($check) {
                return ['status' => "error", 'message' => 'Leave type already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Leave type created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function updateleavetype($id, $data)
    {
        try
        {
            $leavetype = $this->model->find($id);
            if (!$leavetype) {
                return ['status' => "error", 'message' => 'Leave type not found'];
            }
            $leavetype->update($data);
            return ['status' => "success", 'message' => 'Leave type updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deleteleavetype($id)
    {
        try
        {
            $leavetype = $this->model->find($id);
            if (!$leavetype) {
                return ['status' => "error", 'message' => 'Leave type not found'];
            }
            $leavetype->delete();
            return ['status' => "success", 'message' => 'Leave type deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
}
