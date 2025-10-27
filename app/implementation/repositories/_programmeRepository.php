<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iprogrammeInterface;
use App\Models\Programme;
use App\Interfaces\repositories\istrategylogInterface;
use Illuminate\Support\Facades\Auth;
class _programmeRepository implements iprogrammeInterface
{
    /**
     * Create a new class instance.
     */
    protected $programmemodel;
    protected $strategylogrepository;
    public function __construct(Programme $programmemodel,istrategylogInterface $strategylogrepository)
    {
        $this->programmemodel = $programmemodel;
        $this->strategylogrepository = $strategylogrepository;
    }
    public function getprogrammes($strategy_id)
    {
        return $this->programmemodel->where('strategy_id',$strategy_id)->get();
    }
    public function getprogramme($id)
    {
        return $this->programmemodel->where('id',$id)->first();
    }
    public function createprogramme(array $data)
    {
        try {
            $result = $this->programmemodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$result->id,
                'source'=>'programme',
                'old_data'=>null,
                'new_data'=>json_encode($data),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Programme created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function updateprogramme($id,array $data)
    {
        try {
            $checkprogramme = $this->programmemodel->where('id',$id)->first();
            if (!$checkprogramme) {
                return ["status"=>"error","message"=>"Programme not found"];
            }
            $olddata = $checkprogramme;
            $result = $checkprogramme->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'programme',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Programme updated successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteprogramme($id)
    {
        try {
            $checkprogramme = $this->programmemodel->where('id',$id)->first();
            if (!$checkprogramme) {
                return ["status"=>"error","message"=>"Programme not found"];
            }
            $olddata = $checkprogramme;
            $result = $checkprogramme->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'programme',
                'old_data'=>json_encode($olddata),
                'new_data'=>null,
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Programme deleted successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function approveprogramme($id)
    {
        try {
            $checkprogramme = $this->programmemodel->where('id',$id)->first();
            if (!$checkprogramme) {
                return ["status"=>"error","message"=>"Programme not found"];
            }
            $olddata = $checkprogramme;
            $result = $checkprogramme->update(['status'=>'Approved','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'programme',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Programme approved successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function unapproveprogramme($id)
    {
        try {
            $checkprogramme = $this->programmemodel->where('id',$id)->first();
            if (!$checkprogramme) {
                return ["status"=>"error","message"=>"Programme not found"];
            }
            $olddata = $checkprogramme;
            $result = $checkprogramme->update(['status'=>'Draft','approvedby'=>Auth::user()->id]);
            $this->strategylogrepository->createstrategylog([
                'source_id'=>$id,
                'source'=>'programme',
                'old_data'=>json_encode($olddata),
                'new_data'=>json_encode($result),
                'user_id'=>Auth::user()->id,
            ]);
            return ["status"=>"success","message"=>"Programme unapproved successfully"];
        }
        catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
}
