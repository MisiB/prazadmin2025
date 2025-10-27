<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ioutcomeInterface;
use App\Models\Outcome;
use App\Interfaces\repositories\istrategylogInterface;
use Illuminate\Support\Facades\Auth;
class _outcomeRepository implements ioutcomeInterface
{
    /**
     * Create a new class instance.
     */
    protected $outcomemodel;
    protected $strategylogrepository;
    public function __construct(Outcome $outcomemodel,istrategylogInterface $strategylogrepository)
    {
        $this->outcomemodel = $outcomemodel;
        $this->strategylogrepository = $strategylogrepository;
    }
    public function getoutcomes($programme_id){
        return $this->outcomemodel->where('programme_id',$programme_id)->get();

    }
    public function getoutcome($id){
        return $this->outcomemodel->where('id',$id)->first();
    }
    public function createoutcome(array $data){
        try {
            $result = $this->outcomemodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'outcome',
                'old_data'=>null,
                'new_data'=>json_encode($data),
            ]);
            return ["status"=>"success","message"=>"Outcome created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateoutcome($id,array $data){
        try {
            $checkoutcome = $this->outcomemodel->where('id',$id)->first();
            if (!$checkoutcome) {
                return ["status"=>"error","message"=>"Outcome not found"];
            }
            $olddata = $checkoutcome;
            $result = $checkoutcome->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'outcome',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Outcome updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteoutcome($id){
        try {
            $checkoutcome = $this->outcomemodel->where('id',$id)->first();
            if (!$checkoutcome) {
                return ["status"=>"error","message"=>"Outcome not found"];
            }
            $olddata = $checkoutcome;
            $result = $checkoutcome->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'outcome',
                'old_data'=>json_encode($olddata),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Outcome deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function approveoutcome($id){
        try {
            $checkoutcome = $this->outcomemodel->where('id',$id)->first();
            if (!$checkoutcome) {
                return ["status"=>"error","message"=>"Outcome not found"];
            }
            $olddata = $checkoutcome;
            $result = $checkoutcome->update(['status'=>'Approved','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'outcome',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Outcome approved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function unapproveoutcome($id){
        try {
            $checkoutcome = $this->outcomemodel->where('id',$id)->first();
            if (!$checkoutcome) {
                return ["status"=>"error","message"=>"Outcome not found"];
            }
            $olddata = $checkoutcome;
            $result = $checkoutcome->update(['status'=>'Draft','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'outcome',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Outcome unapproved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
}
