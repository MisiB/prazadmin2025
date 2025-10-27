<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Models\Leaverequestapproval;
  
class _leaverequestapprovalRepository implements ileaverequestapprovalInterface
{
    protected $model;
    public function __construct(Leaverequestapproval $model)
    {
        $this->model=$model;
    }
    public function getleaverequestapprovals()
    {
        return $this->model->all();
    }
    public function getleaverequestapprovalsByUserId($userid)
    {
        return $this->model->where('user_id', $userid);
    }
    public function getleaverequestapprovalsByStatus($status)
    {
        return $this->model->where('action', $status);
    }
    //id of approval is equal to the uuid of the request
    public function getleaverequestapproval($requestuuid)
    {
        return $this->model->where('leaverequest_uuid',$requestuuid)->first();
    }
    public function createleaverequestapproval($data)
    {
        try 
        {
            $check=$this->model->where(['leaverequest_uuid' => $data['leaverequest_uuid']])->first();
            if ($check) {
                return ['status' => "error", 'message' => 'Leave approval already exists for this request'];
            }
            $this->model->create($data);
            return ["status"=>"success","message"=>"Leave statement created successfully"];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updateleaverequestapproval($id, $data)
    {
        try
        {
            $leaveapproval = $this->getleaverequestapproval($id);
            if (!$leaveapproval) {
                return ["status" => "error", "message" => "Leave approval not found."];
            }
            $leaveapproval->update($data);
            return ["status" => "success", "message" => "Leave approval updated successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Failed to update leave approval: " . $e->getMessage()];
        }
    }
    public function deleteleaverequestapproval($id)
    {
        try 
        {
            $leaveapproval = $this->getleaverequestapproval($id);
            if (!$leaveapproval) {
                return ["status" => "error", "message" => "Leave approval not found."];
            }
            $leaveapproval->delete();
            return ["status" => "success", "message" => "Leave approval deleted successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Failed to delete leave approval: " . $e->getMessage()];
        }
    }
}
