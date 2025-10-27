<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iadminstoresrequisitionapprovalInterface;
use App\Models\Adminstoresrequisitionapproval;

class _adminstoresrequisitionapprovalRepository implements iadminstoresrequisitionapprovalInterface
{
    protected $model;
    public function __construct(Adminstoresrequisitionapproval $model)
    {
       $this->model=$model;
    }

    public function getadminrequisitionapprovals()
    {
        return $this->model->all();
    }
    public function getadminrequisitionapproval($storesrequisitionuuid)
    {
        return $this->model->where('storesrequisition_uuid', $storesrequisitionuuid)->first();
    }
    public function createadminrequisitionapproval($data)
    {
        try
        {
            $check = $this->getadminrequisitionapproval($data['storesrequisition_uuid']);
            if ($check) {
                return ['status' => "error", 'message' => 'Store requisition already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Store requisition created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updateadminrequisitionapproval($storesrequisition_uuid, $data)
    {
        try
        {
            $check = $this->getadminrequisitionapproval($storesrequisition_uuid);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store requisition not found'];
            }
            $check->update($data);
            return ['status' => "success", 'message' => 'Store requisition updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deleteadminrequisitionapproval($id)
    {
        try
        {
            $check = $this->model->find($id);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store requisition not found'];
            }
            $check->delete();
            return ['status' => "success", 'message' => 'Store requisition deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

}
