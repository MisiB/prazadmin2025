<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ireceiverstoresrequisitionapprovalInterface;
use App\Models\Receiverstoresrequisitionapproval;

class _receiverstoresrequisitionapprovalRepository implements ireceiverstoresrequisitionapprovalInterface
{
    protected $model;
    public function __construct(Receiverstoresrequisitionapproval $model)
    {
       $this->model=$model;
    }

    public function getreceiverrequisitionapprovals()
    {
        return $this->model->all();
    }
    public function getreceiverrequisitionapproval($storesrequisitionuuid)
    {
        return $this->model->where('storesrequisition_uuid', $storesrequisitionuuid)->first();
    }
    public function createreceiverrequisitionapproval($data)
    {
        try
        {
            $check = $this->getreceiverrequisitionapproval($data['storesrequisition_uuid']);
            if ($check) {
                return ['status' => "error", 'message' => 'Issuer stores requisition record already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Issuer stores requisition record created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updatereceiverrequisitionapproval($storesrequisition_uuid, $data)
    {
        try
        {
            $check = $this->getreceiverrequisitionapproval($storesrequisition_uuid);
            if (!$check) {
                return ['status' => "error", 'message' => 'Issuer stores requisition record not found'];
            }
            $check->update($data);
            return ['status' => "success", 'message' => 'Issuer stores requisition record updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deletereceiverrequisitionapproval($id)
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
