<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ioutputInterface;
use App\Models\Output;
use App\Interfaces\repositories\istrategylogInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\Departmentoutput;
class _outputRepository implements ioutputInterface
{
    protected $outputmodel;
    protected $departmentoutputmodel;
    protected $strategylogrepository;
    public function __construct(Output $outputmodel,Departmentoutput $departmentoutputmodel,istrategylogInterface $strategylogrepository)
    {
        $this->outputmodel = $outputmodel;
        $this->departmentoutputmodel = $departmentoutputmodel;
        $this->strategylogrepository = $strategylogrepository;
    }
    public function getoutputs($outcome_id){
        return $this->outputmodel->where('outcome_id',$outcome_id)->get();
    }
    public function getoutput($id){
        return $this->outputmodel->where('id',$id)->first();
    }
    public function createoutput(array $data){
        try {
            $data['createdby'] = Auth::user()->id;
            $result = $this->outputmodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'output',
                'old_data'=>null,
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Output created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateoutput($id,array $data){
        try {
            $checkoutput = $this->outputmodel->where('id',$id)->first();
            if (!$checkoutput) {
                return ["status"=>"error","message"=>"Output not found"];
            }
            $olddata = $checkoutput;
            $data['updatedby'] = Auth::user()->id;
            $result = $checkoutput->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'output',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Output updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteoutput($id){
        try {
            $checkoutput = $this->outputmodel->where('id',$id)->first();
            if (!$checkoutput) {
                return ["status"=>"error","message"=>"Output not found"];
            }
            $olddata = $checkoutput;
            $result = $checkoutput->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'output',
                'old_data'=>json_encode($olddata),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Output deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function approveoutput($id){
        try {
            $checkoutput = $this->outputmodel->where('id',$id)->first();
            if (!$checkoutput) {
                return ["status"=>"error","message"=>"Output not found"];
            }
    
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function unapproveoutput($id){
        try {
            $checkoutput = $this->outputmodel->where('id',$id)->first();
            if (!$checkoutput) {
                return ["status"=>"error","message"=>"Output not found"];
            }
        
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function adddepartmentoutput(array $data){
        try {
            $checkoutput = $this->departmentoutputmodel->where('output_id',$data['output_id'])->where('department_id',$data['department_id'])->first();
            if ($checkoutput) {
                return ["status"=>"error","message"=>"Output already added to department"];
            }
            $result = $this->departmentoutputmodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'departmentoutput',
                'old_data'=>null,
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Output added to department successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updatedepartmentoutput($id,array $data){
        try {
            $checkoutput = $this->departmentoutputmodel->where('id',$id)->first();
            if (!$checkoutput) {
                return ["status"=>"error","message"=>"Output not found"];
            }
            $olddata = $checkoutput;
            $result = $checkoutput->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'departmentoutput',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Output updated in department successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletedepartmentoutput($id){
        try {
            $checkoutput = $this->departmentoutputmodel->where('id',$id)->first();
            if (!$checkoutput) {
                return ["status"=>"error","message"=>"Output not found"];   
            }
            $olddata = $checkoutput;
            $result = $checkoutput->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'output',
                'old_data'=>json_encode($olddata),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,    
            ]);
            return ["status"=>"success","message"=>"Output deleted from department successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function getdepartmentoutput($id){
        return $this->departmentoutputmodel->where('id',$id)->first();
    }
  
}