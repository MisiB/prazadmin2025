<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\istrategylogInterface;
use App\Models\Individualworkplan;
use Illuminate\Support\Facades\Auth;

class _individualworkplanRepository implements individualworkplanInterface
{
    /**
     * Create a new class instance.
     */
    protected $individualworkplan;

    protected $strategylogrepository;

    public function __construct(Individualworkplan $individualworkplan, istrategylogInterface $strategylogrepository)
    {
        $this->individualworkplan = $individualworkplan;
        $this->strategylogrepository = $strategylogrepository;
    }

    public function getindividualworkplans($user_id, $strategy_id, $year)
    {
        return $this->individualworkplan
            ->with('user', 'targetmatrix.target.indicator.departmentoutput.output.outcome.programme')
            ->where('user_id', $user_id)
            ->where('strategy_id', $strategy_id)
            ->where('year', $year)
            ->get();
    }

    public function getsubordinatesworkplans($approver_id, $strategy_id, $year)
    {
        return $this->individualworkplan
            ->with('user', 'targetmatrix.target.indicator.departmentoutput.output.outcome.programme')
            // ->where('approver_id', $approver_id)
            ->where('strategy_id', $strategy_id)
            ->where('year', $year)
            ->where('status', 'PENDING')
            ->get();
    }

    public function createindividualworkplan($data)
    {
        try {
            $result = $this->individualworkplan->create($data);
            $this->strategylogrepository->createstrategylog([
                'source_id' => $result->id,
                'source' => 'individualworkplan',
                'old_data' => null,
                'new_data' => json_encode($data),
                'user_id' => Auth::user()->id,
            ]);

            return ['status' => 'success', 'message' => 'Individual workplan created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateindividualworkplan($id, $data)
    {
        try {
            $individualworkplan = $this->individualworkplan->where('id', $id)->first();

            $individualworkplan->update($data);
            $this->strategylogrepository->createstrategylog([
                'source_id' => $individualworkplan->id,
                'source' => 'individualworkplan',
                'old_data' => json_encode($individualworkplan),
                'new_data' => json_encode($data),
                'user_id' => Auth::user()->id,
            ]);

            return ['status' => 'success', 'message' => 'Individual workplan updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteindividualworkplan($id)
    {
        try {
            $individualworkplan = $this->individualworkplan->where('id', $id)->first();

            $this->strategylogrepository->createstrategylog([
                'source_id' => $individualworkplan->id,
                'source' => 'individualworkplan',
                'old_data' => json_encode($individualworkplan),
                'new_data' => null,
                'user_id' => Auth::user()->id,
            ]);
            $individualworkplan->delete();

            return ['status' => 'success', 'message' => 'Individual workplan deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getindividualworkplan($id)
    {
        return $this->individualworkplan->where('id', $id)->first();
    }

    public function approveindividualworkplan($id, $remarks = null)
    {
        try {
            $individualworkplan = $this->individualworkplan->where('id', $id)->first();

            $olddata = $individualworkplan;

            $updateData = [
                'status' => 'APPROVED',
                'approved_at' => now(),
            ];

            if ($remarks) {
                $updateData['remarks'] = $remarks;
            }

            $individualworkplan->update($updateData);

            $this->strategylogrepository->createstrategylog([
                'source_id' => $individualworkplan->id,
                'source' => 'individualworkplan',
                'old_data' => json_encode($olddata),
                'new_data' => json_encode($individualworkplan),
                'user_id' => Auth::user()->id,
            ]);

            return ['status' => 'success', 'message' => 'Workplan approved successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
