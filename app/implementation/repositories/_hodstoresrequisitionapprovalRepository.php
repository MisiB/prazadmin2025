<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ihodstoresrequisitionapprovalInterface;
use App\Models\Hodstoresrequisitionapproval;

class _hodstoresrequisitionapprovalRepository implements ihodstoresrequisitionapprovalInterface
{
    protected $model;
    public function __construct(Hodstoresrequisitionapproval $model)
    {
       $this->model=$model;
    }

    public function gethodrequisitionapprovals()
    {
        return $this->model->all();
    }
    public function gethodrequisitionapproval($storesrequisitionuuid)
    {
        return $this->model->where('storesrequisition_uuid', $storesrequisitionuuid)->first();
    }
    public function createhodrequisitionapproval($data)
    {
        try
        {
            $check = $this->gethodrequisitionapproval($data['storesrequisition_uuid']);
            if ($check) {
                return ['status' => "error", 'message' => 'Store requisition already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Store requisition created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updatehodrequisitionapproval($storesrequisition_uuid, $data)
    {
        try
        {
            $check = $this->gethodrequisitionapproval($storesrequisition_uuid);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store requisition not found'];
            }
            $check->update($data);
            return ['status' => "success", 'message' => 'Store requisition updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deletehodrequisitionapproval($id)
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
