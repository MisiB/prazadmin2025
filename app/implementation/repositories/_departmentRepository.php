<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\idepartmentInterface;
use App\Models\Department;
use App\Models\Departmentuser;
use Illuminate\Support\Facades\Auth;

class _departmentRepository implements idepartmentInterface
{
    /**
     * Create a new class instance.
     */
    protected $department;
    protected $department_user;
    public function __construct(Department $department,Departmentuser $department_user)
    {
        $this->department = $department;
        $this->department_user = $department_user;
    }

    public function getdepartments()
    {
        return $this->department->with('users')->get();
    }

    public function getdepartment($id)
    {
        return $this->department->with('users')->find($id);
    }

    public function create($department)
    {
        try {
            $exist = $this->department->where('name',$department['name'])->first();
            if($exist){
                return ["status"=>"error","message"=>"Department already exists"];
            }
            $this->department->create($department);
            return ["status"=>"success","message"=>"Department added successfully"];
        } catch (\Exception $th) {
            return ["status"=>"error","message"=>$th->getMessage()];
        }
    }

    public function update($id,$department)
    {
        try {
            $exist = $this->department->where('name',$department['name'])->where('id','!=',$id)->first();
            if($exist){
                return ["status"=>"error","message"=>"Department already exists"];
            }
            $this->department->find($id)->update($department);
            return ["status"=>"success","message"=>"Department updated successfully"];
        } catch (\Exception $th) {
            return ["status"=>"error","message"=>$th->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $this->department->find($id)->delete();
            return ["status"=>"success","message"=>"Department deleted successfully"];
        } catch (\Exception $th) {
            return ["status"=>"error","message"=>$th->getMessage()];
        }
    }
    public function getusers($id)
    {
        return $this->department_user->where('department_id',$id)->get();
    }
    public function getuser($id)
    {
        return $this->department_user->with('user','supervisor')->where('id',$id)->first();
    }
    public function getsupervisor($user_id)
    {
        return $this->department_user->with('user','supervisor')->where('user_id',$user_id)->first();
    }
    public function createuser($data)
    {
        try {
           
            $this->department_user->create([
                "department_id" => $data['department_id'],
                "user_id" => $data['user_id'],
                "position" => $data['position'],
                "isprimary" => $data['isprimary'],
                "reportto" => $data['reportto']
            ]);
            return ["status"=>"success","message"=>"User added successfully"];
        } catch (\Exception $th) {
            return ["status"=>"error","message"=>$th->getMessage()];
        }
    }
    public function updateuser($id,$data)
    {
        try {
            $this->department_user->where('department_id', $id)
                ->where('user_id', $data['user_id'])
                ->update([
                    "position" => $data['position'],
                    "isprimary" => $data['isprimary']
                ]);
            return ["status"=>"success","message"=>"User updated successfully"];
        } catch (\Exception $th) {
            return ["status"=>"error","message"=>$th->getMessage()];
        }
    }
    public function deleteuser($id)
    {
        try {
            $this->department_user->where('id', $id)->delete();
            return ["status"=>"success","message"=>"User deleted successfully"];
        } catch (\Exception $th) {
            return ["status"=>"error","message"=>$th->getMessage()];
        }
    }
    public function getmysubordinates()
    {
        return $this->department_user->with('user')->where('reportto', Auth::user()->id)->get();
    }
}
