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
    public function getleaverequestbyuuid($leaverequestuuid)
    {
        return $this->model->where('leaverequestuuid', $leaverequestuuid)->first();
    } 
    public function getleaverequestsbyuserid($userid,$statusfilter=null,$searchuuid=null)
    {

        $query = $this->model->with('leavetype','leaverequestapproval')->where('user_id', $userid);

        if ($statusfilter) {
            $query->where('status', $statusfilter);
        }
        
        if ($searchuuid) {
            $query->where('leaverequestuuid', $searchuuid);
        }

        return $query->get();
    } 
    public function getfirstleaverequestsbyuserid($userid)
    {
        return $this->model->with('leavetype','leaverequestapproval')->where([['user_id','=', $userid], ['status','<>','C']])->orderBy('startdate', 'asc')->first();
    } 
    public function getlastleaverequestsbyuserid($userid)
    {
        return $this->model->with('leavetype','leaverequestapproval')->where([['user_id','=', $userid], ['status','<>','C']])->orderBy('returndate', 'desc')->first();
    } 
    public function getleaverequestbyuseridandstatus($userid,$status)
    {
        return $this->model->where('user_id', $userid)->where('status',$status)->get();
    }
    public function getleaverequestbystatus($status)
    {
        return $this->model->where('status',$status)->get();
    }
    public function getleaverequestsbyleavetype($leavetypeid)
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
            $exists=$this->getleaverequestbyuseridandstatus($userid,'P');
            if($exists->count() > 0)
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
            $leaverequest = $this->getleaverequestbyuuid($id);
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
