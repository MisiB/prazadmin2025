<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ileavestatementInterface;
use App\Models\Leavestatement;

class _leavestatementRepository implements ileavestatementInterface
{
    protected $model;

    public function __construct(Leavestatement $model)
    {
        $this->model = $model;
    }

    public function getleavestatements()
    {
        return $this->model->with('leavetype','user')->get();
    }
    public function getleavestatementByLeaveType($leavetypeid)
    {
        return $this->model->with('leavetype','user')->where('leavetype_id', $leavetypeid)->get();
    }
    public function getleavestatementByUser($userid)
    {
        return $this->model->where('user_id', $userid)->get();
    }
    public function getleavestatement($id)
    {
        return $this->model->find($id);
    }    
    public function createleavestatement($data)
    {
        try 
        {
            $check=$this->model->where(['user_id' => $data['user_id'], 'leavetype_id' => $data['leavetype_id']])->first();
            if ($check) {
                return ['status' => "error", 'message' => 'Leave statement already exists for this user and leave type'];
            }
            $this->model->create($data);
            return ["status"=>"success","message"=>"Leave statement created successfully"];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updateleavestatement($id, $data)
    {
        try
        {
            $leavestatement = $this->model->find($id);
            if (!$leavestatement) {
                return ['status' => "error", 'message' => 'Leave statement not found'];
            }
            $leavestatement->update($data);
            return ['status' => "success", 'message' => 'Leave statement updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];

        }
    }
    public function deleteleavestatement($id)
    {
        try
        {
            $leavestatement = $this->model->find($id);
            if (!$leavestatement) {
                return ['status' => "error", 'message' => 'Leave statement not found'];
            }
            $leavestatement->delete();
            return ['status' => "success", 'message' => 'Leave statement deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];

        }
    }
    public function getleavestatementByUserAndLeaveType($userid, $leavetypeid)
    {
        return $this->model->where('user_id', $userid)->where('leavetype_id', $leavetypeid)->first();
    }
    public function getleavestatementByUserIdAndLeaveName($userid, $leavename)
    {
        return $this->model->with('leavetype','user')->whereHas('user',function($user) use (&$userid)
        {
            $user->where('id', $userid);
        })->whereHas('leavetype', function($leavetype) use (&$leavename)
        {
            $leavetype->where('name', $leavename);
        })->first();
    }
}
