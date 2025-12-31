<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\itsallowanceconfigInterface;
use App\Models\GradeBand;
use App\Models\TsAllowanceConfig;
use App\Models\TsAllowanceConfigAudit;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class _tsallowanceconfigRepository implements itsallowanceconfigInterface
{
    protected $tsallowanceconfig;

    protected $gradeband;

    protected $tsallowanceconfigaudit;

    public function __construct(
        TsAllowanceConfig $tsallowanceconfig,
        GradeBand $gradeband,
        TsAllowanceConfigAudit $tsallowanceconfigaudit
    ) {
        $this->tsallowanceconfig = $tsallowanceconfig;
        $this->gradeband = $gradeband;
        $this->tsallowanceconfigaudit = $tsallowanceconfigaudit;
    }

    public function getconfigs($search = null)
    {
        $query = $this->tsallowanceconfig
            ->with('gradeBand', 'currency', 'creator', 'approver')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('gradeBand', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15);
    }

    public function getconfig($id)
    {
        return $this->tsallowanceconfig
            ->with('gradeBand', 'currency', 'creator', 'approver', 'audits.changedBy')
            ->find($id);
    }

    public function getactiveconfigs()
    {
        return $this->tsallowanceconfig
            ->with('gradeBand', 'currency')
            ->where('status', 'ACTIVE')
            ->orderBy('grade_band_id')
            ->get();
    }

    public function getconfigsbycategory($category)
    {
        // This method is deprecated as we no longer use categories
        // Keeping for backward compatibility but returning empty collection
        return collect();
    }

    public function getconfigsbygradeband($gradeBandId)
    {
        return $this->tsallowanceconfig
            ->with('currency')
            ->where('grade_band_id', $gradeBandId)
            ->get();
    }

    public function getactiveconfigbycategoryandgrade($category, $gradeBandId)
    {
        // This method is deprecated as we no longer use categories
        // Return active config for grade band only
        return $this->tsallowanceconfig
            ->with('gradeBand', 'currency')
            ->where('grade_band_id', $gradeBandId)
            ->where('status', 'ACTIVE')
            ->where('effective_from', '<=', now())
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    public function createconfig($data)
    {
        try {
            DB::beginTransaction();

            // Set default status
            $data['status'] = $data['status'] ?? 'INACTIVE';
            $data['created_by'] = Auth::user()->id;

            // If activating, deactivate existing active configs for same grade band
            if ($data['status'] === 'ACTIVE') {
                $this->tsallowanceconfig
                    ->where('grade_band_id', $data['grade_band_id'])
                    ->where('status', 'ACTIVE')
                    ->update(['status' => 'INACTIVE']);
            }

            $config = $this->tsallowanceconfig->create($data);

            // Create audit record if rate changed
            if (isset($data['previous_rate'])) {
                $this->tsallowanceconfigaudit->create([
                    'ts_allowance_config_id' => $config->id,
                    'previous_rate' => $data['previous_rate'],
                    'new_rate' => $data['out_of_station_subsistence_rate'] ?? 0,
                    'grade_band_id' => $data['grade_band_id'],
                    'changed_by' => Auth::user()->id,
                    'change_date' => now(),
                    'effective_date' => $data['effective_from'] ?? now(),
                    'change_reason' => $data['change_reason'] ?? null,
                ]);
            }

            DB::commit();

            return ['status' => 'success', 'message' => 'Allowance configuration created successfully', 'data' => $config];
        } catch (Exception $e) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateconfig($id, $data)
    {
        try {
            DB::beginTransaction();

            $config = $this->tsallowanceconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration not found'];
            }

            // Store previous rate for audit
            $previousRate = $config->out_of_station_subsistence_rate;

            // Only allow updating if status is INACTIVE, DRAFT or PENDING_APPROVAL
            if ($config->status === 'ACTIVE' && ! isset($data['status'])) {
                return ['status' => 'error', 'message' => 'Cannot update active configuration. Deactivate first or create a new version.'];
            }

            $config->update($data);

            // Create audit record if rate changed
            $newRate = $data['out_of_station_subsistence_rate'] ?? $config->out_of_station_subsistence_rate;
            if ($previousRate != $newRate) {
                $this->tsallowanceconfigaudit->create([
                    'ts_allowance_config_id' => $config->id,
                    'previous_rate' => $previousRate,
                    'new_rate' => $newRate,
                    'grade_band_id' => $config->grade_band_id,
                    'changed_by' => Auth::user()->id,
                    'change_date' => now(),
                    'effective_date' => $config->effective_from,
                    'change_reason' => $data['change_reason'] ?? null,
                ]);
            }

            DB::commit();

            return ['status' => 'success', 'message' => 'Allowance configuration updated successfully', 'data' => $config];
        } catch (Exception $e) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function activateconfig($id, $data)
    {
        try {
            DB::beginTransaction();

            $config = $this->tsallowanceconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration not found'];
            }

            // Deactivate existing active configs for same grade band
            $this->tsallowanceconfig
                ->where('grade_band_id', $config->grade_band_id)
                ->where('status', 'ACTIVE')
                ->where('id', '!=', $id)
                ->update(['status' => 'INACTIVE']);

            // Activate this config
            $config->status = 'ACTIVE';
            $config->approved_by = $data['approved_by'] ?? Auth::user()->id;
            $config->approved_at = now();
            $config->save();

            // Create audit record
            $this->tsallowanceconfigaudit->create([
                'ts_allowance_config_id' => $config->id,
                'previous_rate' => null,
                'new_rate' => $config->out_of_station_subsistence_rate,
                'grade_band_id' => $config->grade_band_id,
                'changed_by' => Auth::user()->id,
                'change_date' => now(),
                'effective_date' => $config->effective_from,
                'approval_reference' => $data['approval_reference'] ?? null,
                'change_reason' => 'Configuration activated',
            ]);

            DB::commit();

            return ['status' => 'success', 'message' => 'Allowance configuration activated successfully', 'data' => $config];
        } catch (Exception $e) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deactivateconfig($id)
    {
        try {
            $config = $this->tsallowanceconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration not found'];
            }

            $config->status = 'INACTIVE';
            $config->save();

            return ['status' => 'success', 'message' => 'Allowance configuration deactivated successfully', 'data' => $config];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitforapproval($id, $data)
    {
        try {
            $config = $this->tsallowanceconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration not found'];
            }

            $config->status = 'PENDING_APPROVAL';
            $config->save();

            return ['status' => 'success', 'message' => 'Configuration submitted for approval successfully', 'data' => $config];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveconfig($id, $data)
    {
        try {
            DB::beginTransaction();

            $config = $this->tsallowanceconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration not found'];
            }

            if (! in_array($config->status, ['PENDING_APPROVAL', 'INACTIVE', 'DRAFT'])) {
                return ['status' => 'error', 'message' => 'Configuration cannot be approved from current status'];
            }

            // Deactivate existing active configs for same grade band
            $this->tsallowanceconfig
                ->where('grade_band_id', $config->grade_band_id)
                ->where('status', 'ACTIVE')
                ->where('id', '!=', $id)
                ->update(['status' => 'INACTIVE']);

            // Activate this config
            $config->status = 'ACTIVE';
            $config->approved_by = Auth::user()->id;
            $config->approved_at = now();
            $config->save();

            // Create audit record
            $this->tsallowanceconfigaudit->create([
                'ts_allowance_config_id' => $config->id,
                'previous_rate' => null,
                'new_rate' => $config->out_of_station_subsistence_rate,
                'grade_band_id' => $config->grade_band_id,
                'changed_by' => Auth::user()->id,
                'change_date' => now(),
                'effective_date' => $config->effective_from,
                'approval_reference' => $data['approval_reference'] ?? null,
                'change_reason' => 'Configuration approved',
            ]);

            DB::commit();

            return ['status' => 'success', 'message' => 'Configuration approved and activated successfully', 'data' => $config];
        } catch (Exception $e) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectconfig($id, $data)
    {
        try {
            $config = $this->tsallowanceconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration not found'];
            }

            if ($config->status !== 'PENDING_APPROVAL') {
                return ['status' => 'error', 'message' => 'Configuration is not pending approval'];
            }

            $config->status = 'INACTIVE';
            $config->notes = ($config->notes ?? '')."\nRejected: ".($data['comment'] ?? 'No reason provided');
            $config->save();

            return ['status' => 'success', 'message' => 'Configuration rejected successfully', 'data' => $config];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getconfigaudits($configId)
    {
        return $this->tsallowanceconfigaudit
            ->with('changedBy', 'gradeBand')
            ->where('ts_allowance_config_id', $configId)
            ->orderBy('change_date', 'desc')
            ->get();
    }

    public function getallgradebands()
    {
        return $this->gradeband
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    public function getgradeband($id)
    {
        return $this->gradeband->find($id);
    }
}
