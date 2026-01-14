<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ischoolInterface;
use App\Models\School;
use Illuminate\Support\Collection;

class _schoolRepository implements ischoolInterface
{
    protected $model;

    public function __construct(School $model)
    {
        $this->model=$model;
    }
    public function createschool($data)
    { 
        try
        {
            $schoolexists = $this->getschoolbynumber($data['schoolnumber']);
            if($schoolexists)
            {
                return ["status"=>"error", "message"=>"School already exists"];
            }
            $this->model->create($data);
            return ["status"=>"success", "message"=>"School created successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    public function getschools():Collection
    {
        return $this->model->orderBy("name","asc")->get();
    }
    public function getschoolbyid($id)
    {
        return $this->model->find($id);
    }
    public function getschoolbynumber($schoolnumber)
    {
        return $this->model->where("school_number", $schoolnumber)->first();
    }
    public function getschoolbynameornumber($schoolname=null, $schoolnumber=null)
    { 
        if($schoolname!==null)
        {
            return $this->model->orWhere('name', 'like', '%' . $schoolname . '%')->first();
        }elseif($schoolnumber!=null)
        {
            return $this->model->where("school_number", $schoolnumber)->first();
        }else{
            return false;
        }

    }
    public function updateschool($schoolnumber, $data)
    {
        try
        {
            $schoolexists = $this->getschoolbynumber($schoolnumber);
            if(!$schoolexists)
            {
                return ["status"=>"error", "message"=>"School does not exist"];
            }
            $this->model->update($data);
            return ["status"=>"success", "message"=>"School updated successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    public function deleteschool($schoolnumber)
    { 
        try
        {
            $schoolexists = $this->getschoolbynumber($schoolnumber);
            if(!$schoolexists)
            {
                return ["status"=>"error", "message"=>"School does not exist"];
            }
            $schoolexists->delete();
            return ["status"=>"success", "message"=>"School deleted successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }

}
