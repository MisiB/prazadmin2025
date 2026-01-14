<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ischoolexpensecategoryInterface;
use App\Models\Schoolexpensecategory;
use Illuminate\Support\Collection;

class _schoolexpensecategoryRepository implements ischoolexpensecategoryInterface
{
    protected $model;
    public function __construct(Schoolexpensecategory $model)
    {
        $this->model=$model;
    }
    
    public function createexpensecategory($data)
    {
        try {
            $expensecategoryexists=$this->model->getexpensecategorybyname($data['name']);
            if ($expensecategoryexists) {
                return ["status"=>"error", "message"=>"Expense category already exists"];
            }
            $this->model->create($data);
            return ["status"=>"success", "message"=>"Expense category created successfully"];
            
        } catch (\Exception $e) {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    public function getexpensecategories():Collection
    {
        return $this->model->orderBy("name","asc")->get();
    }
    
    public function getexpensecategorybyid($id)
    {
        return $this->model->where("id", $id)->first();
    }
    public function getexpensecategorybyname($name)
    {
        return $this->model->where("name", $name)->first();
    }
    public function updateexpensecategory($id, $data)
    {
        
        try {
            $expensecategoryexists = $this->getexpensecategorybyid($id);
            if(!$expensecategoryexists)
            {
                return ["status"=>"error", "message"=>"Expense category does not exist"];
            }
            $this->model->update($data);
            return ["status"=>"success", "message"=>"Expense category updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
    public function deleteexpensecategory($id)
    {
        try {
            $expensecategoryexists = $this->getexpensecategorybyid($id);
            if(!$expensecategoryexists)
            {
                return ["status"=>"error", "message"=>"Expense category does not exist"];
            }
            $expensecategoryexists->delete();
            return ["status"=>"success", "message"=>"Expense category deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error", "message"=>$e->getMessage()];
        }
    }
}
