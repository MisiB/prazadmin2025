<?php

namespace App\implementation\repositories;

use App\interfaces\repositories\iworkplanInterface;
use App\Models\Departmentuser;
use App\Models\Individualoutput;
use App\Models\Individualoutputassignee;
use App\Models\Individualoutputbreakdown;
use App\Models\Strategy;
use App\Models\Strategyprogramme;
use App\Models\Strategyprogrammeoutcome;
use App\Models\Strategyprogrammeoutcomeindicator;
use App\Models\Strategysubprogramme;
use App\Models\Strategysubprogrammeoutput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class _workplanRepository implements iworkplanInterface
{
    /**
     * Create a new class instance.
     */
    protected $individualoutput;
    protected $individualoutputassignee;
    protected $individualoutputbreakdown;
    protected $subprogrammeoutput;
   protected $strategy;
   protected $strategyprogramme;
   protected $strategyprogrammeoutcome;
   protected $strategyprogrammeoutcomeindicator;
   protected $subprogramme;
   protected $departmentuser;
   protected $subprogrammeoutputindicator;
    public function __construct(Individualoutput $individualoutput,Strategysubprogrammeoutput $subprogrammeoutput, Strategy $strategy, Strategyprogramme $programme, Strategyprogrammeoutcome $outcome, Strategyprogrammeoutcomeindicator $indicator, Strategysubprogramme $subprogramme,Individualoutputassignee $individualoutputassignee,Departmentuser $departmentuser,Individualoutputbreakdown $individualoutputbreakdown)
    {
        $this->individualoutput = $individualoutput;
        $this->subprogrammeoutput = $subprogrammeoutput;
        $this->strategy = $strategy;
        $this->strategyprogramme = $programme;
        $this->strategyprogrammeoutcome = $outcome;
        $this->strategyprogrammeoutcomeindicator = $indicator;
        $this->subprogramme = $subprogramme;
        $this->individualoutputassignee = $individualoutputassignee;
        $this->departmentuser = $departmentuser;
        $this->individualoutputbreakdown = $individualoutputbreakdown;
    }

    public function getworkplans($strategy_id,$year)
    {
        $user = Auth::user()->department;

        /**
         * get  strategy
         */
        $strategy =  $this->strategy->where("id", $strategy_id)->first();
        if ($strategy == null) {
           return null;
        }
        /**
         * get  programmes
         */
        $programmes = $this->strategyprogramme->where("strategy_id", $strategy_id)->get();
        if (count($programmes) == 0) {
           return null;
        }
        $data = [];
        foreach ($programmes as $programme) {
           /**
            * get  strategyprogrammeoutcomes
            */
        
          
           $strategyprogrammeoutcomes = $this->strategyprogrammeoutcome->where("programme_id", $programme->id)->get();
  
           foreach ($strategyprogrammeoutcomes as $strategyprogrammeoutcome) {
          
              $strategyprogrammeoutcomeindicators = $this->strategyprogrammeoutcomeindicator->where("programmeoutcome_id", $strategyprogrammeoutcome->id)->get();
            
              foreach ($strategyprogrammeoutcomeindicators as $strategyprogrammeoutcomeindicator) {
                 $subprogrammes = $this->subprogramme->where("programmeoutcomeindicator_id", $strategyprogrammeoutcomeindicator->id)->where("department_id", $user->department_id)->get();
                if(count($subprogrammes) > 0){
                  
                 foreach ($subprogrammes as $subprogramme) {
                    $subprogrammeoutputs = $this->subprogrammeoutput->where("subprogramme_id", $subprogramme->id)->get();
                   
                   
                   
                    foreach ($subprogrammeoutputs as $subprogrammeoutput) {
                       $outputindicatormeasures = [];
                       /*** check if user is not head of department */
                       if(!Auth::user()->department->isprimary){

                        /** get  manager user id */
                        $departmentuser = $this->departmentuser->where("user_id", Auth::user()->id)->first();
                        if($departmentuser == null){
                            continue;
                        }
                        $manageruser = $departmentuser->reportto;
                          //dd($manageruser);
                        /**get manager workplan with  indicators assigned to me , assinged indicators  should me outs */
                        $userindividualoutputs = $this->individualoutput
                        ->with("assignee")
                        ->wherehas("assignee",function($query){
                            $query->where("user_id",Auth::user()->id);
                        })
                        ->where("subprogrammeoutput_id", $subprogrammeoutput->id)
                        ->where("user_id", $manageruser)
                        ->get();
                        //dd($userindividualoutputs);
                              $myworkplans = $this->individualoutput
                              ->where("subprogrammeoutput_id", $subprogrammeoutput->id)
                              ->where("user_id", Auth::user()->id)
                              ->get();
                            /** loop through userindividualoutputs */
                            foreach ($userindividualoutputs as $userindividualoutput) {
                            

                        $data[]=[
                          "id"=>$subprogrammeoutput->id,
                         "subprogrammeoutputindicator" => $userindividualoutput->indicator,
                         "subprogrammeoutput" => $userindividualoutput->output,
                         "subprogrammeoutputquantity" => $userindividualoutput->assignee->first()->target,
                         "subprogrammeouttarget" => $userindividualoutput->assignee->first()->target,
                         "subprogrammeoutallowablevariance" => $userindividualoutput->assignee->first()->variance,
                         "supervisoroutput_id"=>$userindividualoutput->id,
                         "userindividualoutputs"=>$myworkplans
                      ];
                    }
                       }else{
                       /**
                        * get assigned indicators
                        */
                  
                        $userindividualoutputs = $this->individualoutput
                        ->where("subprogrammeoutput_id", $subprogrammeoutput->id)
                        ->where("user_id",Auth::user()->id)
                        ->get();
                     
                          $data[]=[
                           "id"=>$subprogrammeoutput->id,
                          "subprogrammeoutputindicator" => $subprogrammeoutput->indicator,
                          "subprogrammeoutput" => $subprogrammeoutput->output,
                          "subprogrammeoutputquantity" => $subprogrammeoutput->quantity,
                          "subprogrammeouttarget" => $subprogrammeoutput->target,
                          "subprogrammeoutallowablevariance" => $subprogrammeoutput->variance,
                          "supervisoroutput_id"=>null,
                          "userindividualoutputs"=>$userindividualoutputs,
                       ];
                      }
                    }
                
                 }
               
           }
           }
           }
        }        
        return $data; 
    }
    public function createworkplan($data){
     try{
      $uuid = Str::uuid();
        $this->individualoutput->create([
            "subprogrammeoutput_id"=>$data["subprogrammeoutput_id"],
            "output"=>$data["output"],
            "indicator"=>$data["indicator"],
            "target"=>$data["target"],
            "variance"=>$data["variance"],
            "weightage"=>$data["weightage"],
            "parent_id"=>$data["parent_id"],
            "createdby"=>Auth::user()->id,
            "user_id"=>Auth::user()->id,
            "uuid"=>$uuid
        ]);
        return ["status"=>"success","message"=>"Workplan created successfully"];
     }catch(\Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
     }
    }
    public function updateworkplan($id,$data){
     try{
        $this->individualoutput->where("id",$id)->update([
            "subprogrammeoutput_id"=>$data["subprogrammeoutput_id"],
            "output"=>$data["output"],
            "indicator"=>$data["indicator"],
            "target"=>$data["target"],
            "variance"=>$data["variance"],
            "weightage"=>$data["weightage"],
            "parent_id"=>$data["parent_id"],
            "user_id"=>Auth::user()->id
        ]);
        return ["status"=>"success","message"=>"Workplan updated successfully"];
     }catch(\Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
     }
    }
    public function deleteworkplan($id){
     try{
        $this->individualoutput->where("id",$id)->delete();
        return ["status"=>"success","message"=>"Workplan deleted successfully"];
     }catch(\Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
     }
    }
    public function getworkplan($id){
        $workplan = $this->individualoutput->where("id",$id)->first();
        return $workplan;
    }
    public function getworkplanbreakdown($id){
        $breakdown = $this->individualoutputbreakdown->where("id",$id)->first();
        return $breakdown;
    }
    public function getworkplanbreakdownlist($id){
        $breakdown = $this->individualoutputbreakdown->where("individualoutput_id",$id)->get();
        return $breakdown;
    }
    public function getworkplabreakdownbyuser($user_id,$year){
        $breakdown = $this->individualoutputbreakdown->where("created_by",$user_id)->whereYear("created_at",$year)->get();
        return $breakdown;
    }
    public function createworkplanbreakdown($data){
        try{
            $this->individualoutputbreakdown->create([
                "individualoutput_id"=>$data["individualoutput_id"],
                "month"=>$data["month"],
                "contribution"=>$data["contribution"],
                "description"=>$data["description"],
                "output"=>$data["output"],
                "created_by"=>Auth::user()->id
            ]);
            return ["status"=>"success","message"=>"Workplan breakdown created successfully"];
         }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
         }
    }
    public function updateworkplanbreakdown($id,$data){
        try{
            $this->individualoutputbreakdown->where("id",$id)->update([
                "month"=>$data["month"],
                "contribution"=>$data["contribution"],
                "description"=>$data["description"],
                "output"=>$data["output"],
                "updated_by"=>Auth::user()->id
            ]);
            return ["status"=>"success","message"=>"Workplan breakdown updated successfully"];
         }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
         }
    }
    public function deleteworkplanbreakdown($id){
        try{
            $this->individualoutputbreakdown->where("id",$id)->delete();
            return ["status"=>"success","message"=>"Workplan breakdown deleted successfully"];
         }catch(\Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
         }
    }
    public function getworkplanassignees($id){
        $assignees = $this->individualoutputassignee->with("user")->where("individualoutput_id",$id)->get();
        return $assignees;
    }

    public function getworkplanassignee($id){
        $assignee = $this->individualoutputassignee->where("id",$id)->first();
        return $assignee;
    }
    public function createworkplanassignee($data){
      try{
    
        $this->individualoutputassignee->create([
            "individualoutput_id"=>$data["individualoutput_id"],
            "user_id"=>$data["user_id"],
            "target"=>$data["target"],
            "variance"=>$data["variance"],
            "created_by"=>Auth::user()->id
        ]);
        return ["status"=>"success","message"=>"Workplan assignee created successfully"];
      }catch(\Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
      }

    }
    public function updateworkplanassignee($id,$data){
      try{
        $this->individualoutputassignee->where("id",$id)->update([
            "target"=>$data["target"],
        ]);
        return ["status"=>"success","message"=>"Workplan assignee updated successfully"];
      }catch(\Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
      }
    }
    public function deleteworkplanassignee($id){
      try{
        $this->individualoutputassignee->where("id",$id)->delete();
        return ["status"=>"success","message"=>"Workplan assignee deleted successfully"];
      }catch(\Exception $e){
        return ["status"=>"error","message"=>$e->getMessage()];
      }
    }
}
