<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ischoolmonthlyreturnInterface;
use App\Models\Schoolmonthlyreturn;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class _schoolmonthlyreturnRepository implements ischoolmonthlyreturnInterface
{
    protected $model;
    public function __construct(Schoolmonthlyreturn $model)
    {
        $this->model=$model;
    }
    public function createmonthlyreturn($data)
    { 
        try
        {
            $this->model->create($data);
            return ["status"=>"success", "message"=>"Monthly return created successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    
    public function getmonthlyreturns($status, $year=null, $month=null):Collection
    {
        if($year==null)
        {
            $year= Carbon::now()->year;
        }       
        if($month==null)
        {
            $month = Carbon::now()->englishMonth;
        }
        return $this->model->with('schoolmonthlyreturndatas')->where('status', 'APPROVED')->where('year', $year)->where('month', $month)->get();
    }
    public function getmonthlyreturnbyid($id)
    {
        return $this->model->with('schoolmonthlyreturndatas')->where("id", $id)->first();
    }
    public function getmonthlyreturnsbyschoolnumber($schoolnumber, $status, $year=null, $month=null):Collection
    {
        if($year==null)
        {
            $year= Carbon::now()->year;
        }       
        if($month==null)
        {
            $month = Carbon::now()->englishMonth;
        }
        /**
         * Note that 'school_id === 'school_number'
         * */
        return $this->model->where("school_id", $schoolnumber)->where('status', $status)->where('year', $year)->where('month', $month)->get();
    }

    public function getmonthlyreturnbyexpensecategory($categoryid)
    {
        return $this->model->where("school_id", $categoryid)->first();
    }
    public function getmonthlyreturnbycurrency($ccurrencyid)
    {
        return [];
    }
    public function updatemonthlyreturn($id, $data)
    {
        try
        {
            $schoolexists = $this->getmonthlyreturnbyid($id);
            if(!$schoolexists)
            {
                return ["status"=>"error", "message"=>"School monthly return does not exist"];
            }
            $this->model->update($data);
            return ["status"=>"success", "message"=>"School monthly return updated successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    public function deletemonthlyreturn($id)
    {
        try
        {
            
            return ["status"=>"success", "message"=>"School updated successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
}
