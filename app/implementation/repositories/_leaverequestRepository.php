<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ileaverequestInterface;
use App\Models\Leaverequest;

class _leaverequestRepository implements ileaverequestInterface
{
    protected $model;
    public function __construct(Leaverequest $model)
    {
        $this->model = $model;
    }

    public function getleaverequests()
    {
        return $this->model->all();
    }
    public function getleaverequestByUuid($leaverequestuuid)
    {
        return $this->model->where('leaverequestuuid', $leaverequestuuid)->first();
    }
    public function getleaverequestsByUserId($userid)
    {
        return $this->model->with('leavetype','leaverequestapproval')->where('user_id', $userid)->get();
    }
    public function getleaverequestByUserIdAndStatus($userid,$status)
    {
        return $this->model->where('user_id', $userid)->where('status',$status)->first();
    }
    public function getleaverequestByStatus($status)
    {
        return $this->model->where('status',$status)->first();
    }
    public function getleaverequestsByLeavetype($leavetypeid)
    {
        return $this->model->where('leavetype_id', $leavetypeid)->get();
    }
    public function getleaverequest($id)
    {
        return $this->model->find($id);
    }
    public function createleaverequest($userid,$data)
    {
        try {
            $exists=$this->getleaverequestByUserIdAndStatus($userid,'PENDING');
            if($exists)
            {
                return ["status" => "error", "message" => "You already have a pending leave request."];
            }
            $this->model->create($data);
            return ["status" => "success", "message" => "Leave request created successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Failed to create leave request: " . $e->getMessage()];
        }
    }
    public function updateleaverequest($id, $data)
    {
        try
        {
            $leaverequest = $this->getleaverequest($id);
            if (!$leaverequest) {
                return ["status" => "error", "message" => "Leave request not found."];
            }
            $leaverequest->update($data);
            return ["status" => "success", "message" => "Leave request updated successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Failed to update leave request: " . $e->getMessage()];
        }
    }
    public function deleteleaverequest($id)
    {
        try 
        {
            $leaverequest = $this->getleaverequest($id);
            if (!$leaverequest) {
                return ["status" => "error", "message" => "Leave request not found."];
            }
            $leaverequest->delete();
            return ["status" => "success", "message" => "Leave request deleted successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Failed to delete leave request: " . $e->getMessage()];
        }
    }
}
