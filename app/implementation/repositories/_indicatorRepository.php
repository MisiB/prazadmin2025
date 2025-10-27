<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iIndicatorInterface;
use App\Models\Indicator;
use App\Models\Target;
use App\Interfaces\repositories\istrategylogInterface;
use Illuminate\Support\Facades\Auth;
class _indicatorRepository implements iIndicatorInterface
{
    /**
     * Create a new class instance.
     */
    protected $indicatormodel;
    protected $targetmodel;
    protected $strategylogrepository;
    public function __construct(Indicator $indicatormodel,Target $targetmodel,istrategylogInterface $strategylogrepository)
    {
        $this->indicatormodel = $indicatormodel;
        $this->targetmodel = $targetmodel;
        $this->strategylogrepository = $strategylogrepository;
    }
    public function getindicators($departmentoutput_id){
        return $this->indicatormodel->where('departmentoutput_id',$departmentoutput_id)->get();
    }    public function getindicator($id){
        return $this->indicatormodel->where('id',$id)->first();
    }
    public function createindicator(array $data){
        try {
            $result = $this->indicatormodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'indicator',
                'old_data'=>null,
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Indicator created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateindicator($id,array $data){
        try {
            $checkindicator = $this->indicatormodel->where('id',$id)->first();
            if (!$checkindicator) {
                return ["status"=>"error","message"=>"Indicator not found"];
            }
            $olddata = $checkindicator;
            $result = $checkindicator->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'indicator',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Indicator updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteindicator($id){
        try {
            $checkindicator = $this->indicatormodel->where('id',$id)->first();
            if (!$checkindicator) {
                return ["status"=>"error","message"=>"Indicator not found"];
            }
            $olddata = $checkindicator;
            $result = $checkindicator->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'indicator',
                'old_data'=>json_encode($olddata),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Indicator deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function approveindicator($id){
        try {
            $checkindicator = $this->indicatormodel->where('id',$id)->first();
            if (!$checkindicator) {
                return ["status"=>"error","message"=>"Indicator not found"];
            }
            $olddata = $checkindicator;
            $result = $checkindicator->update(['status'=>'Approved','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'indicator',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Indicator approved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function unapproveindicator($id){
        try {
            $checkindicator = $this->indicatormodel->where('id',$id)->first();
            if (!$checkindicator) {
                return ["status"=>"error","message"=>"Indicator not found"];
            }
            $olddata = $checkindicator;
            $result = $checkindicator->update(['status'=>'Draft','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'indicator',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Indicator unapproved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function addtarget(array $data){
        try {
            $result = $this->targetmodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'target',
                'old_data'=>null,
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Target created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updatetarget($id,array $data){
        try {
            $checktarget = $this->targetmodel->where('id',$id)->first();
            if (!$checktarget) {
                return ["status"=>"error","message"=>"Target not found"];
            }
            $olddata = $checktarget;
            $result = $checktarget->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'target',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Target updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deletetarget($id){
        try {
            $checktarget = $this->targetmodel->where('id',$id)->first();
            if (!$checktarget) {
                return ["status"=>"error","message"=>"Target not found"];
            }
            $olddata = $checktarget;
            $result = $checktarget->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'target',
                'old_data'=>json_encode($olddata),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Target deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function gettarget($id){
        return $this->targetmodel->where('id',$id)->first();
    }
    public function gettargetbyindicator($indicator_id){
        return $this->targetmodel->where('indicator_id',$indicator_id)->get();
    }
   
}
