<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\isubprogrammeoutInterface;
use App\Models\Strategy;
use App\Models\Strategyprogramme;
use App\Models\Strategyprogrammeoutcome;
use App\Models\Strategyprogrammeoutcomeindicator;
use App\Models\Strategysubprogramme;
use App\Models\Strategysubprogrammeoutput;
use Illuminate\Support\Facades\Auth;

class _subprogrammeoutputRepository implements isubprogrammeoutInterface
{
   /**
    * Create a new class instance.
    */

   protected $subprogrammeoutput;
   protected $strategy;
   protected $strategyprogramme;
   protected $strategyprogrammeoutcome;
   protected $strategyprogrammeoutcomeindicator;
   protected $subprogramme;
   protected $subprogrammeoutputindicator;
   public function __construct(Strategysubprogrammeoutput $subprogrammeoutput, Strategy $strategy, Strategyprogramme $programme, Strategyprogrammeoutcome $outcome, Strategyprogrammeoutcomeindicator $indicator, Strategysubprogramme $subprogramme)
   {
      $this->subprogrammeoutput = $subprogrammeoutput;
      $this->strategy = $strategy;
      $this->strategyprogramme = $programme;
      $this->strategyprogrammeoutcome = $outcome;
      $this->strategyprogrammeoutcomeindicator = $indicator;
      $this->subprogramme = $subprogramme;
   }
   public function getsubprogrammeoutputs($strategy_id, $year) 
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
      $array = [];
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
                 
                 
                  $data=[];
                  foreach ($subprogrammeoutputs as $subprogrammeoutput) {
                      
                        $data[]=[
                         "id"=>$subprogrammeoutput->id,
                        "subprogrammeoutputindicator" => $subprogrammeoutput->indicator,
                        "subprogrammeoutput" => $subprogrammeoutput->output,
                        "subprogrammeoutputquantity" => $subprogrammeoutput->quantity,
                        "subprogrammeouttarget" => $subprogrammeoutput->target,
                        "subprogrammeoutallowablevariance" => $subprogrammeoutput->variance,
                     ];
                  }
                  $array[]=[
                  "subprogramme_id" => $subprogramme->id,
                  "programme_id"=>$programme->id,
                  "programmecode"=>$programme->code,
                  "programme" => $programme->title,
                  "programmeoutcome_id" => $strategyprogrammeoutcome->id,
                  "programmeoutcome" => $strategyprogrammeoutcome->title,
                  "programmeoutcomeindicator" => $strategyprogrammeoutcomeindicator->indicator,
                  "programmeoutcomeindicatortarget" => $strategyprogrammeoutcomeindicator->target,
                  "programmeoutcomeindicatoruom" => $strategyprogrammeoutcomeindicator->uom,
                  "programmeoutcomeindicatorvariance" => $strategyprogrammeoutcomeindicator->variance,
                  "programmeoutcomeindicatorvarianceuom" => $strategyprogrammeoutcomeindicator->varianceuom,
                  "subprogramme" => $subprogramme->department->name,
                  "subprogramme_id" => $subprogramme->id,
                  "weightage"=>$subprogramme->weightage,
                  "department" => $subprogramme->department->name,
                  "data"=>$data
                  ];
               }
             
         }
         }
         }
      }
      return $array;
   }
   public function getsubprogrammeoutputbydepartment($strategy_id,$year,$department_id){
      return $this->subprogrammeoutput->where("department_id", $department_id)->where("strategy_id", $strategy_id)->get();
   }
   public function createsubprogrammeoutput(array $data)
   {
      try {
         $user = Auth::user()->id;
         $data["createdby"] = $user;
         $this->subprogrammeoutput->create($data);
         return ["status"=>"success","message"=>"Subprogramme output created successfully"];
      } catch (\Exception $th) {
         return ["status"=>"error","message"=>$th->getMessage()];
      }
   }
   public function updatesubprogrammeoutput($id, array $data)
   {
      try {
         $data["updatedby"] = Auth::user()->id;
         $this->subprogrammeoutput->find($id)->update($data);
         return ["status"=>"success","message"=>"Subprogramme output updated successfully"];
      } catch (\Exception $th) {
         return ["status"=>"error","message"=>$th->getMessage()];
      }
   }
   public function getsubprogrammeoutput($id)
   {
         return $this->subprogrammeoutput->find($id);
    
   }
   public function deletesubprogrammeoutput($id)
   {
      try {
         $this->subprogrammeoutput->find($id)->delete();
         return ["status"=>"success","message"=>"Subprogramme output deleted successfully"];
      } catch (\Exception $th) {
         return ["status"=>"error","message"=>$th->getMessage()];
      }
   }
}
