<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibudgetconfigurationInterface;
use App\Models\Expensecategory;
use App\Models\Sourceoffund;
use App\Models\Sourceoffundtype;

class _budgetconfigurationRepository implements ibudgetconfigurationInterface
{
    /**
     * Create a new class instance.
     */
    protected $expensecategory;
    protected $sourceoffund;
    protected $sourceoffundtype;
    public function __construct(Expensecategory $expensecategory, Sourceoffund $sourceoffund, Sourceoffundtype $sourceoffundtype)
    {
        $this->expensecategory = $expensecategory;
        $this->sourceoffund = $sourceoffund;
        $this->sourceoffundtype = $sourceoffundtype;
    }

    public function getexpensecategories(){
        return $this->expensecategory->get();
    }

    public function getexpensecategory($id){
        return $this->expensecategory->find($id);
    }

    public function createexpensecategory($data){
        try{
        $exists = $this->expensecategory->where('name', $data['name'])->exists();
        if($exists){
            return ["status"=>"error","message"=>"Expense Category Already Exists"];
        }
         $this->expensecategory->create($data);
         return ["status"=>"success","message"=>"Expense Category Created Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Expense Category Not Created"];
        }
    }

    public function updateexpensecategory($id,$data){
        try{
            $exists = $this->expensecategory->where('name', $data['name'])->where('id','!=',$id)->exists();
            if($exists){
                return ["status"=>"error","message"=>"Expense Category Already Exists"];
            }
            $this->expensecategory->find($id)->update($data);
            return ["status"=>"success","message"=>"Expense Category Updated Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Expense Category Not Updated"];
        }
    }

    public function deleteexpensecategory($id){
        try{
            $this->expensecategory->find($id)->delete();
            return ["status"=>"success","message"=>"Expense Category Deleted Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Expense Category Not Deleted"];
        }
    }

    public function getsourceoffundtypes(){
        return $this->sourceoffundtype->get();
    }

    public function getsourceoffundtype($id){
        return $this->sourceoffundtype->find($id);
    }

    public function createsourceoffundtype($data){
        try{
            $exists = $this->sourceoffundtype->where('name', $data['name'])->exists();
            if($exists){
                return ["status"=>"error","message"=>"Source of Fund Type Already Exists"];
            }
            $this->sourceoffundtype->create($data);
            return ["status"=>"success","message"=>"Source of Fund Type Created Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Source of Fund Type Not Created"];
        }
    }

    public function updatesourceoffundtype($id,$data){
        try{
            $exists = $this->sourceoffundtype->where('name', $data['name'])->where('id','!=',$id)->exists();
            if($exists){
                return ["status"=>"error","message"=>"Source of Fund Type Already Exists"];
            }
            $this->sourceoffundtype->find($id)->update($data);
            return ["status"=>"success","message"=>"Source of Fund Type Updated Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Source of Fund Type Not Updated"];
        }
    }

    public function deletesourceoffundtype($id){
        try{
            $this->sourceoffundtype->find($id)->delete();
            return ["status"=>"success","message"=>"Source of Fund Type Deleted Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Source of Fund Type Not Deleted"];
        }
    }

    public function getsourceoffunds(){
        return $this->sourceoffund->get();
    }

    public function getsourceoffund($id){
        return $this->sourceoffund->find($id);
    }

    public function createsourceoffund($data){
        try{
            $exists = $this->sourceoffund->where('name', $data['name'])->exists();
            if($exists){
                return ["status"=>"error","message"=>"Source of Fund Already Exists"];
            }
            $this->sourceoffund->create($data);
            return ["status"=>"success","message"=>"Source of Fund Created Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Source of Fund Not Created"];
        }
    }

    public function updatesourceoffund($id,$data){
        try{
            $exists = $this->sourceoffund->where('name', $data['name'])->where('id','!=',$id)->exists();
            if($exists){
                return ["status"=>"error","message"=>"Source of Fund Already Exists"];
            }
            $this->sourceoffund->find($id)->update($data);
            return ["status"=>"success","message"=>"Source of Fund Updated Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Source of Fund Not Updated"];
        }
    }

    public function deletesourceoffund($id){
        try{
            $this->sourceoffund->find($id)->delete();
            return ["status"=>"success","message"=>"Source of Fund Deleted Successfully"];
        }catch(\Exception $e){
            return ["status"=>"error","message"=>"Source of Fund Not Deleted"];
        }
    }
}
