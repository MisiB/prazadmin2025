<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\istrategyInterface;
use App\Models\Strategy;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\repositories\istrategylogInterface;
class _strategyRepository implements istrategyInterface
{
    /**
     * Create a new class instance.
     */
    protected $strategymodel;
    protected $strategylogrepository;
    public function __construct(Strategy $strategymodel,istrategylogInterface $strategylogrepository)
    {
        $this->strategymodel = $strategymodel;
        $this->strategylogrepository = $strategylogrepository;
    }
    public function getstrategies()
    {
        return $this->strategymodel->with('approver','creator')->get();
    }
    public function getstrategy($id)
    {
        return $this->strategymodel->where('id',$id)->first();
    }
    public function getstrategybyuuid($uuid,$year)
    {
        return $this->strategymodel->with(['programmes.outcomes.outputs.departmentoutputs.department','programmes.outcomes.outputs.departmentoutputs.indicators.targets.targetmatrices','programmes.outcomes.outputs.departmentoutputs.indicators.targets'=>function($query)use($year){
            $query->where('year',$year);
        }])->where('uuid',$uuid)->first();
    }
    public function getstrategybydepartment($strategy_id,$department_id,$year){
    
        return $this->strategymodel->with(['programmes.outcomes.outputs.departmentoutputs'=>function($query)use($department_id){            
                $query->where('department_id',$department_id);
            },'programmes.outcomes.outputs.departmentoutputs.indicators.targets'=>function($query)use($year){
                $query->where('year',$year);
            },'programmes.outcomes.outputs.departmentoutputs.indicators.targets.targetmatrices'])->where('id',$strategy_id)->first();
       
    }
    public function createstrategy(array $data)
    {
        try {
            $result = $this->strategymodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'strategy',
                'old_data'=>null,
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);

            return ["status"=>"success","message"=>"Strategy created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function gettargetmatrixbystrategy($strategy_id,$department_id,$year){
        $data= $this->strategymodel->with(['programmes.outcomes.outputs.departmentoutputs'=>function($query)use($department_id){            
            $query->where('department_id',$department_id);
        },'programmes.outcomes.outputs.departmentoutputs.indicators.targets'=>function($query)use($year){
            $query->where('year',$year);
        },'programmes.outcomes.outputs.departmentoutputs.indicators.targets.targetmatrices'])->where('id',$strategy_id)->first();
        return $data;
    }
    public function updatestrategy($id,array $data)
    {
        try {
             $checkstrategy = $this->strategymodel->where('id',$id)->first();
            if (!$checkstrategy) {
                return ["status"=>"error","message"=>"Strategy not found"];
            }
            if($checkstrategy->status != "Draft"){
                return ["status"=>"error","message"=>"You are not authorized to update this strategy"];
            }
            $olddata = $checkstrategy;
            $this->strategymodel->where('id',$id)->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'strategy',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Strategy updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletestrategy($id)
    {
        try {
            $checkstrategy = $this->strategymodel->where('id',$id)->first();
            if (!$checkstrategy) {
                return ["status"=>"error","message"=>"Strategy not found"];
            }
            if($checkstrategy->status != "Draft"){
                return ["status"=>"error","message"=>"You are not authorized to delete this strategy"];
            }
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'strategy',
                'old_data'=>json_encode($checkstrategy),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,
            ]);
            $this->strategymodel->where('id',$id)->delete();
            return ["status"=>"success","message"=>"Strategy deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function approvestrategy($id)
    {
        try {
            $checkstrategy = $this->strategymodel->where('id',$id)->first();
            if (!$checkstrategy) {
                return ["status"=>"error","message"=>"Strategy not found"];
            }
            if($checkstrategy->status != "Draft"){
                return ["status"=>"error","message"=>"You are not authorized to approve this strategy"];
            }
           $result = $this->strategymodel->where('id',$id)->update(['status'=>'Approved','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'strategy',
                'old_data'=>json_encode($checkstrategy),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Strategy approved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function unapprovestrategy($id)
    {
        try {
            $checkstrategy = $this->strategymodel->where('id',$id)->first();
            if (!$checkstrategy) {
                return ["status"=>"error","message"=>"Strategy not found"];
            }
            if($checkstrategy->status != "Approved"){
                return ["status"=>"error","message"=>"You are not authorized to unapprove this strategy"];
            }
            $result = $this->strategymodel->where('id',$id)->update(['status'=>'Draft','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'strategy',
                'old_data'=>json_encode($checkstrategy),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
          
            return ["status"=>"success","message"=>"Strategy unapproved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function copy($id,$data)
    {
        try {
            $checkstrategy = $this->strategymodel->where('id',$id)->first();
            if (!$checkstrategy) {
                return ["status"=>"error","message"=>"Strategy not found"];
            }
            $this->strategymodel->where('id',$id)->first()->copy($data);
            return ["status"=>"success","message"=>"Strategy copied successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
}
