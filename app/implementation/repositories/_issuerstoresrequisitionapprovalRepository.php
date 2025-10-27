<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iissuerstoresrequisitionapprovalInterface;
use App\Models\Issuerstoresrequisitionapproval;

class _issuerstoresrequisitionapprovalRepository implements iissuerstoresrequisitionapprovalInterface
{
    protected $model;
    public function __construct(Issuerstoresrequisitionapproval $model)
    {
       $this->model=$model;
    }

    public function getissuerrequisitionapprovals()
    {
        return $this->model->all();
    }
    public function getissuerrequisitionapproval($storesrequisitionuuid)
    {
        return $this->model->where('storesrequisition_uuid', $storesrequisitionuuid)->first();
    }
    public function createissuerrequisitionapproval($data)
    {
        try
        {
            $check = $this->getissuerrequisitionapproval($data['storesrequisition_uuid']);
            if ($check) {
                return ['status' => "error", 'message' => 'Issuer stores requisition record already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Issuer stores requisition record created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updateissuerrequisitionapproval($storesrequisition_uuid, $data)
    {
        try
        {
            $check = $this->getissuerrequisitionapproval($storesrequisition_uuid);
            if (!$check) {
                return ['status' => "error", 'message' => 'Issuer stores requisition record not found'];
            }
            $check->update($data);
            return ['status' => "success", 'message' => 'Issuer stores requisition record updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deleteissuerrequisitionapproval($id)
    {
        try
        {
            $check = $this->model->find($id);
            if (!$check) {
                return ['status' => "error", 'message' => 'Issuer stores requisition record not found'];
            }
            $check->delete();
            return ['status' => "success", 'message' => 'Issuer stores requisition record deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
}
