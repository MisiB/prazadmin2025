<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iTargetmatrixInterface;
use App\Models\Targetmatrix;
use App\Interfaces\repositories\istrategylogInterface;
use Illuminate\Support\Facades\Auth;

class _targetmatrixRepository implements iTargetmatrixInterface
{
    protected $targetmatrixmodel;
    protected $strategylogrepository;

    public function __construct(Targetmatrix $targetmatrixmodel, istrategylogInterface $strategylogrepository)
    {
        $this->targetmatrixmodel = $targetmatrixmodel;
        $this->strategylogrepository = $strategylogrepository;
    }

    public function gettargetmatrices($target_id)
    {
        return $this->targetmatrixmodel->where('target_id', $target_id)->get();
    }

    public function gettargetmatrix($id)
    {
        return $this->targetmatrixmodel->where('id', $id)->first();
    }

    public function createtargetmatrix(array $data)
    {
        try {
            $data['createdby'] = Auth::user()->id;
            $checktarget = $this->targetmatrixmodel->where('target_id',$data['target_id'])->where('month',$data['month'])->first();
            if ($checktarget) {
                return ["status" => "error", "message" => "Target matrix already exists"];
            }
            $result = $this->targetmatrixmodel->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id' => $result->id,
                'source' => 'targetmatrix',
                'old_data' => null,
                'new_data' => json_encode($data),
                'user_id' => Auth::user()->id,
            ]);
            return ["status" => "success", "message" => "Target matrix created successfully"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function updatetargetmatrix($id, array $data)
    {
        try {
            $checktargetmatrix = $this->targetmatrixmodel->where('id', $id)->first();
            if (!$checktargetmatrix) {
                return ["status" => "error", "message" => "Target matrix not found"];
            }
            $olddata = $checktargetmatrix;
            $result = $checktargetmatrix->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id' => $id,
                'source' => 'targetmatrix',
                'old_data' => json_encode($olddata),
                'new_data' => json_encode($result),
                'user_id' => Auth::user()->id,
            ]);
            return ["status" => "success", "message" => "Target matrix updated successfully"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function deletetargetmatrix($id)
    {
        try {
            $checktargetmatrix = $this->targetmatrixmodel->where('id', $id)->first();
            if (!$checktargetmatrix) {
                return ["status" => "error", "message" => "Target matrix not found"];
            }
            $olddata = $checktargetmatrix;
            $result = $checktargetmatrix->delete();
            $this->strategylogrepository->createstrategylog([
                'source_id' => $id,
                'source' => 'targetmatrix',
                'old_data' => json_encode($olddata),
                'new_data' => null,
                'user_id' => Auth::user()->id,
            ]);
            return ["status" => "success", "message" => "Target matrix deleted successfully"];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}

