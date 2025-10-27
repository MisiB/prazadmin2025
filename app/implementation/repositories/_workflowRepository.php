<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iworkflowInterface;
use App\Models\Workflow;
use App\Models\Workflowparameter;
use Exception;

class _workflowRepository implements iworkflowInterface
{
    /**
     * Create a new class instance.
     */
    protected $workflow;
    protected $workflowparameter;
    public function __construct(Workflow $workflow,Workflowparameter $workflowparameter)
    {
        $this->workflow = $workflow;
        $this->workflowparameter = $workflowparameter;
    }
    public function getworkflows(){
        return $this->workflow->paginate(10);
    }
    public function getworkflow($id){
        return $this->workflow->find($id);
    }
    public function getworkflowbystatus($status){
        return $this->workflow->with("workflowparameters")->where("name", $status)->first();
    }
    public function createworkflow($data){
        try{
            $check = $this->workflow->where("name", $data["name"])->exists();
            if($check){
                return ["status"=>"error","message"=>"Workflow Already Exists"];
            }
            $this->workflow->create($data);
            return ["status"=>"success","message"=>"Workflow Created Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateworkflow($id,$data){
        try{
            $check = $this->workflow->where("name", $data["name"])->where("id", "!=", $id)->exists();
            if($check){
                return ["status"=>"error","message"=>"Workflow Already Exists"];
            }
            $this->workflow->find($id)->update($data);
            return ["status"=>"success","message"=>"Workflow Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteworkflow($id){
        try{
            $this->workflow->find($id)->delete();
            return ["status"=>"success","message"=>"Workflow Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getworkflowparameters($id){
        return $this->workflowparameter->with("permission")->where("workflow_id", $id)->get();
    }
    public function getworkflowparameter($id){
        return $this->workflowparameter->with("permission")->find($id);
    }
    public function createworkflowparameter($data){
        try{
            $check = $this->workflowparameter->where("status", $data["status"])->exists();
            if($check){
                return ["status"=>"error","message"=>"Workflow Parameter Already Exists"];
            }
            $this->workflowparameter->create($data);
            return ["status"=>"success","message"=>"Workflow Parameter Created Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateworkflowparameter($id,$data){
        try{
            $check = $this->workflowparameter->where("status", $data["status"])->where("id", "!=", $id)->exists();
            if($check){
                return ["status"=>"error","message"=>"Workflow Parameter Already Exists"];
            }
            $this->workflowparameter->find($id)->update($data);
            return ["status"=>"success","message"=>"Workflow Parameter Updated Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteworkflowparameter($id){
        try{
            $this->workflowparameter->find($id)->delete();
            return ["status"=>"success","message"=>"Workflow Parameter Deleted Successfully"];
        }catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
}
