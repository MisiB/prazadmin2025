<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ischoolmonthlyreturndataInterface;
use App\Models\Schoolmonthlyreturndata;

class _schoolmonthlyreturndataRepository implements ischoolmonthlyreturndataInterface
{    protected $model;
    public function __construct(Schoolmonthlyreturndata $model)
    {
        $this->model=$model;
    }
    public function createmonthlyreturndata($data)
    { 
        try
        {
            $this->model->create($data);
            return ["status"=>"success", "message"=>"Monthly return data saved successfully"];
        }catch(\Exception $e) 
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    public function getmonthlyreturndatabyid($id)
    {
        return $this->model->where("id", $id)->first();
    }
    
    public function getmonthlyreturndatabyreturnid($monthlyreturnid, $perPage=null)
    {
        if($perPage==null)
        {
            $perPage=10;
        }
        return $this->model->where("schoolmonthlyretun_id", $monthlyreturnid)->paginate($perPage);
    }

    public function getmonthlyreturndatabysourceoffund($sourceoffund)
    {
        return $this->model->where("sourceoffund", $sourceoffund)->first();
    }
    
    public function getmonthlyreturndatabycurrencyid($currencyid)
    {
        return $this->model->where("currency_id", $currencyid)->first();
    }

    public function updatemonthlyreturndata($id, $data)
    {
        try
        {
            $monthlyreturndataexists = $this->getmonthlyreturndatabyid($id);
            if(!$monthlyreturndataexists)
            {
                return ["status"=>"error", "message"=>"School monthly return data does not exist"];
            }
            $this->model->update($data);
            return ["status"=>"success", "message"=>"School monthly return data updated successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }

    public function deletemonthlyreturndata($id)
    {
        try
        {
            $monthlyreturndataexists = $this->getmonthlyreturndatabyid($id);
            if(!$monthlyreturndataexists)
            {
                return ["status"=>"error", "message"=>"School monthly return data does not exist"];
            }
            $monthlyreturndataexists->delete();
            return ["status"=>"success", "message"=>"School monthly return data deleted successfully"];
        }catch(\Exception $e)
        {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
}
