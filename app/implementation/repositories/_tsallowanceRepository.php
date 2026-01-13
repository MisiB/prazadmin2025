<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\itsallowanceconfigInterface;
use App\Interfaces\repositories\itsallowanceInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Models\Departmentuser;
use App\Models\TsAllowance;
use App\Models\TsAllowanceApproval;
use App\Models\User;
use App\Models\Workflow;
use App\Notifications\TsAllowanceAlert;
use App\Notifications\TsAllowanceSendBack;
use App\Notifications\TsAllowanceUpdate;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class _tsallowanceRepository implements itsallowanceInterface
{
    protected $tsallowance;

    protected $tsallowanceapproval;

    protected $workflow;

    protected $departmentuser;

    protected $userrepo;

    protected $departmentrepo;

    protected $tsallowanceconfigrepo;

    public function __construct(
        TsAllowance $tsallowance,
        TsAllowanceApproval $tsallowanceapproval,
        Workflow $workflow,
        Departmentuser $departmentuser,
        iuserInterface $userrepo,
        idepartmentInterface $departmentrepo,
        itsallowanceconfigInterface $tsallowanceconfigrepo
    ) {
        $this->tsallowance = $tsallowance;
        $this->tsallowanceapproval = $tsallowanceapproval;
        $this->workflow = $workflow;
        $this->departmentuser = $departmentuser;
        $this->userrepo = $userrepo;
        $this->departmentrepo = $departmentrepo;
        $this->tsallowanceconfigrepo = $tsallowanceconfigrepo;
    }

    public function getallowances($year, $search = null)
    {
        $query = $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department', 'applicant')
            ->whereNotIn('status', ['DRAFT'])
            ->whereYear('created_at', $year);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('reason_for_allowances', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getdeptallowances($year, $departmentId = null, $search = null)
    {
        $query = $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department', 'applicant')
            ->whereNotIn('status', ['DRAFT'])
            ->whereYear('created_at', $year);

        // Filter by department if departmentId is provided
        if ($departmentId && $departmentId != 0) {
            // Get all user IDs that belong to this department
            $deptMemberIds = $this->departmentuser
                ->where('department_id', $departmentId)
                ->pluck('user_id')
                ->toArray();

            $query->whereIn('applicant_user_id', $deptMemberIds);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('reason_for_allowances', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getalldeptallowances($year, $departmentId = null, $search = null)
    {
        $query = $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department', 'applicant')
            ->whereNotIn('status', ['DRAFT'])
            ->whereYear('created_at', $year);

        // Filter by department if departmentId is provided
        if ($departmentId && $departmentId != 0) {
            // Get all user IDs that belong to this department
            $deptMemberIds = $this->departmentuser
                ->where('department_id', $departmentId)
                ->pluck('user_id')
                ->toArray();

            $query->whereIn('applicant_user_id', $deptMemberIds);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('reason_for_allowances', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function getallallowances($year)
    {
        return $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department', 'applicant')
            ->whereNotIn('status', ['DRAFT'])
            ->whereYear('created_at', $year)
            ->get();
    }

    public function getallowance($id)
    {
        return $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department', 'applicant', 'hod', 'ceo', 'financeOfficer', 'currency', 'exchangeRate', 'approvals.approver', 'approvals.workflowParameter')
            ->find($id);
    }

    public function getallowancebyuuid($uuid)
    {
        return $this->tsallowance
            ->with('workflow.workflowparameters', 'department', 'applicant', 'hod', 'ceo', 'financeOfficer', 'currency', 'exchangeRate', 'approvals.approver', 'approvals.workflowParameter')
            ->where('uuid', $uuid)
            ->first();
    }

    public function getallowancesbyapplicant($userId, $year, $search = null)
    {
        $query = $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department')
            ->where('applicant_user_id', $userId)
            ->whereYear('created_at', $year);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('reason_for_allowances', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getallowancesbystatus($year, $status)
    {
        return $this->tsallowance
            ->with('workflow.workflowparameters.permission', 'department', 'applicant', 'approvals.approver')
            ->whereYear('created_at', $year)
            ->where('status', $status)
            ->paginate(10);
    }

    public function getallowancesbyworkflowparameter($year)
    {
        $data = $this->workflow
            ->with([
                'workflowparameters.permission',
                'workflowparameters.tsallowanceapprovals' => function ($query) use ($year) {
                    $query->whereYear('created_at', $year)
                        ->whereNotIn('status', ['DRAFT', 'REJECTED']);
                },
            ])
            ->where('name', 'ts_allowances')
            ->first();

        return $data?->workflowparameters ?? collect();
    }

    public function createallowance($data)
    {
        try {
            $workflowname = config('workflow.ts_allowances', 'ts_allowances');
            $workflow = $this->workflow->where('name', $workflowname)->first();

            if ($workflow == null) {
                return ['status' => 'error', 'message' => 'Workflow Not Defined'];
            }

            $user = $this->userrepo->getuser($data['applicant_user_id']);
            $departmentuser = $this->departmentuser->with('department', 'supervisor')
                ->where('user_id', $data['applicant_user_id'])
                ->first();

            if (! $departmentuser) {
                return ['status' => 'error', 'message' => 'User Department Not Found'];
            }

            $applicationNumber = 'TSA'.date('Y').random_int(1000, 9999999);

            $data['uuid'] = Str::uuid()->toString();
            $data['workflow_id'] = $workflow->id;
            $data['application_number'] = $applicationNumber;
            $data['department_id'] = $departmentuser->department_id;
            $data['full_name'] = $user->name;
            $data['status'] = 'DRAFT';
            $data['applicant_user_id'] = $data['applicant_user_id'];

            $this->tsallowance->create($data);

            return ['status' => 'success', 'message' => 'T&S Allowance Application Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateallowance($id, $data)
    {
        try {
            $record = $this->tsallowance->find($id);

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Application Cannot be Modified After Submission'];
            }

            // Only allow updating applicant section fields in draft
            $allowedFields = [
                'job_title',
                'grade',
                'trip_start_date',
                'trip_end_date',
                'reason_for_allowances',
                'out_of_station_subsistence',
                'overnight_allowance',
                'bed_allowance',
                'breakfast',
                'lunch',
                'dinner',
                'fuel',
                'toll_gates',
                'mileage_estimated_distance',
                'number_of_days',
            ];

            $updateData = array_intersect_key($data, array_flip($allowedFields));

            // Calculate totals
            if (isset($updateData['out_of_station_subsistence']) || isset($updateData['overnight_allowance']) ||
                isset($updateData['bed_allowance']) || isset($updateData['breakfast']) ||
                isset($updateData['lunch']) || isset($updateData['dinner']) ||
                isset($updateData['fuel']) || isset($updateData['toll_gates'])) {

                $updateData['calculated_subtotal'] =
                    ($updateData['out_of_station_subsistence'] ?? $record->out_of_station_subsistence) +
                    ($updateData['overnight_allowance'] ?? $record->overnight_allowance) +
                    ($updateData['bed_allowance'] ?? $record->bed_allowance) +
                    ($updateData['breakfast'] ?? $record->breakfast) +
                    ($updateData['lunch'] ?? $record->lunch) +
                    ($updateData['dinner'] ?? $record->dinner) +
                    ($updateData['fuel'] ?? $record->fuel) +
                    ($updateData['toll_gates'] ?? $record->toll_gates);

                $updateData['balance_due'] = $updateData['calculated_subtotal'];
            }

            $record->update($updateData);

            return ['status' => 'success', 'message' => 'T&S Allowance Application Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteallowance($id)
    {
        try {
            $record = $this->tsallowance->where('id', $id)->first();

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Application Cannot be Deleted After Submission'];
            }

            $record->delete();

            return ['status' => 'success', 'message' => 'T&S Allowance Application Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitallowance($id)
    {
        try {
            $record = $this->tsallowance->with('workflow.workflowparameters')->where('id', $id)->first();

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Application Already Submitted'];
            }

            // Validate required fields
            $requiredFields = [
                'job_title',
                'grade',
                'trip_start_date',
                'trip_end_date',
                'reason_for_allowances',
            ];

            foreach ($requiredFields as $field) {
                if (empty($record->$field)) {
                    return ['status' => 'error', 'message' => "Field {$field} is required"];
                }
            }

            // Get first workflow parameter (HOD/Supervisor step)
            $firstWorkflowParameter = $record->workflow->workflowparameters->where('order', 1)->first();

            if (! $firstWorkflowParameter) {
                return ['status' => 'error', 'message' => 'Workflow Configuration Error'];
            }

            $record->status = $firstWorkflowParameter->status; // Should be "SUBMITTED"
            $record->applicant_digital_signature = true;
            $record->submission_date = now();
            $record->save();

            // Notify approvers
            $users = User::permission($firstWorkflowParameter->permission->name)->get();

            if ($users->count() > 0) {
                $array = [
                    'application_number' => $record->application_number,
                    'full_name' => $record->full_name,
                    'department' => $record->department->name ?? 'N/A',
                    'trip_start_date' => $record->trip_start_date?->format('Y-m-d') ?? 'N/A',
                    'trip_end_date' => $record->trip_end_date?->format('Y-m-d') ?? 'N/A',
                    'reason_for_allowances' => $record->reason_for_allowances,
                    'calculated_subtotal' => $record->calculated_subtotal ?? 0,
                    'status' => $record->status,
                    'uuid' => $record->uuid,
                ];

                Notification::send($users, new TsAllowanceAlert(collect($array)));
            }

            return ['status' => 'success', 'message' => 'T&S Allowance Application Submitted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recommend($id, $data)
    {
        try {
            $record = $this->tsallowance
                ->with('workflow.workflowparameters.permission', 'applicant')
                ->where('id', $id)
                ->first();

            $workflowParameter = $record->workflow->workflowparameters
                ->where('status', $record->status)
                ->first();

            if (! $workflowParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            // Check if this step was already processed in the CURRENT submission cycle
            // If there's a SEND_BACK anywhere after the last APPROVED at this step, allow reprocessing
            $mostRecentApprovalAtStep = $this->tsallowanceapproval
                ->where('ts_allowance_id', $id)
                ->where('workflowparameter_id', $workflowParameter->id)
                ->latest()
                ->first();

            // Check if there's been a SEND_BACK after the last approval at this step
            $mostRecentSendBack = $this->tsallowanceapproval
                ->where('ts_allowance_id', $id)
                ->where('status', 'SEND_BACK')
                ->latest()
                ->first();

            // Block only if: there's an APPROVED at this step AND no SEND_BACK happened after it
            if ($mostRecentApprovalAtStep && $mostRecentApprovalAtStep->status === 'APPROVED') {
                // Check if SEND_BACK happened after this approval
                $sendBackAfterApproval = $mostRecentSendBack && $mostRecentSendBack->created_at > $mostRecentApprovalAtStep->created_at;

                if (! $sendBackAfterApproval) {
                    return ['status' => 'error', 'message' => 'Already Processed at This Step'];
                }
            }

            // Move to next step
            $count = $record->workflow->workflowparameters->count();
            $users = new Collection;

            if ($workflowParameter->order + 1 <= $count) {
                $nextParameter = $record->workflow->workflowparameters
                    ->where('order', $workflowParameter->order + 1)
                    ->first();

                $record->status = $nextParameter->status;
                $users = User::permission($nextParameter->permission->name)->get();
            } else {
                $record->status = 'RECOMMENDED';
            }

            // Update recommendation section
            $record->recommendation_decision = 'RECOMMENDED';
            $record->hod_name = Auth::user()->name;
            $record->hod_designation = $data['hod_designation'] ?? Auth::user()->job_title ?? 'HOD';
            $record->hod_digital_signature = true;
            $record->recommendation_date = now();
            $record->hod_user_id = Auth::user()->id;
            $record->hod_comment = $data['comment'] ?? '';
            $record->save();

            // Create approval record
            $this->tsallowanceapproval->create([
                'ts_allowance_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'APPROVED',
                'comment' => $data['comment'] ?? '',
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Notify next approvers
            if ($users->count() > 0) {
                $array = [
                    'application_number' => $record->application_number,
                    'full_name' => $record->full_name,
                    'department' => $record->department->name ?? 'N/A',
                    'trip_start_date' => $record->trip_start_date?->format('Y-m-d') ?? 'N/A',
                    'trip_end_date' => $record->trip_end_date?->format('Y-m-d') ?? 'N/A',
                    'reason_for_allowances' => $record->reason_for_allowances,
                    'calculated_subtotal' => $record->calculated_subtotal ?? 0,
                    'status' => $record->status,
                    'uuid' => $record->uuid,
                ];

                Notification::send($users, new TsAllowanceAlert(collect($array)));
            }

            // Notify applicant
            $updateData = [
                'step' => $workflowParameter->name ?? $workflowParameter->status,
                'status' => 'RECOMMENDED',
                'comment' => $data['comment'] ?? '',
            ];
            $record->applicant->notify(new TsAllowanceUpdate($updateData));

            return ['status' => 'success', 'message' => 'T&S Allowance Recommended Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectrecommendation($id, $data)
    {
        try {
            $record = $this->tsallowance
                ->with('workflow.workflowparameters.permission', 'applicant')
                ->where('id', $id)
                ->first();

            $workflowParameter = $record->workflow->workflowparameters
                ->where('status', $record->status)
                ->first();

            if (! $workflowParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            $record->status = 'REJECTED';
            $record->recommendation_decision = 'NOT_RECOMMENDED';
            $record->hod_comment = $data['comment'];
            $record->save();

            // Add comment
            $comments = $record->comments ?? collect();
            $comments->push([
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'comment' => $data['comment'],
                'created_at' => now()->toDateTimeString(),
            ]);
            $record->comments = $comments;
            $record->save();

            // Create approval record
            $this->tsallowanceapproval->create([
                'ts_allowance_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'REJECTED',
                'comment' => $data['comment'],
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Notify applicant of rejection
            $updateData = [
                'step' => $workflowParameter->name ?? $workflowParameter->status,
                'status' => 'REJECTED',
                'comment' => $data['comment'],
            ];
            $record->applicant->notify(new TsAllowanceUpdate($updateData));

            return ['status' => 'success', 'message' => 'T&S Allowance Rejected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approve($id, $data)
    {
        try {
            $record = $this->tsallowance
                ->with('workflow.workflowparameters.permission', 'applicant')
                ->where('id', $id)
                ->first();

            $workflowParameter = $record->workflow->workflowparameters
                ->where('status', $record->status)
                ->first();

            if (! $workflowParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            // Check if this step was already processed in the CURRENT submission cycle
            // If there's a SEND_BACK anywhere after the last APPROVED at this step, allow reprocessing
            $mostRecentApprovalAtStep = $this->tsallowanceapproval
                ->where('ts_allowance_id', $id)
                ->where('workflowparameter_id', $workflowParameter->id)
                ->latest()
                ->first();

            // Check if there's been a SEND_BACK after the last approval at this step
            $mostRecentSendBack = $this->tsallowanceapproval
                ->where('ts_allowance_id', $id)
                ->where('status', 'SEND_BACK')
                ->latest()
                ->first();

            // Block only if: there's an APPROVED at this step AND no SEND_BACK happened after it
            if ($mostRecentApprovalAtStep && $mostRecentApprovalAtStep->status === 'APPROVED') {
                // Check if SEND_BACK happened after this approval
                $sendBackAfterApproval = $mostRecentSendBack && $mostRecentSendBack->created_at > $mostRecentApprovalAtStep->created_at;

                if (! $sendBackAfterApproval) {
                    return ['status' => 'error', 'message' => 'Already Processed at This Step'];
                }
            }

            // Move to next step
            $count = $record->workflow->workflowparameters->count();
            $users = new Collection;

            if ($workflowParameter->order + 1 <= $count) {
                $nextParameter = $record->workflow->workflowparameters
                    ->where('order', $workflowParameter->order + 1)
                    ->first();

                $record->status = $nextParameter->status;
                $users = User::permission($nextParameter->permission->name)->get();
            } else {
                // All approvals complete - set to AWAITING_PAYMENT for payment voucher
                $record->status = 'AWAITING_PAYMENT';
            }

            // Update approval section if CEO
            if ($workflowParameter->status === 'CEO_APPROVAL' || stripos($workflowParameter->name, 'CEO') !== false) {
                $record->approval_decision = 'APPROVED';
                $record->ceo_digital_signature = true;
                $record->approval_date = now();
                $record->ceo_user_id = Auth::user()->id;
                $record->ceo_comment = $data['comment'] ?? '';
            }

            $record->save();

            // Create approval record
            $this->tsallowanceapproval->create([
                'ts_allowance_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'APPROVED',
                'comment' => $data['comment'] ?? '',
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Notify next approvers
            if ($users->count() > 0) {
                $array = [
                    'application_number' => $record->application_number,
                    'full_name' => $record->full_name,
                    'department' => $record->department->name ?? 'N/A',
                    'trip_start_date' => $record->trip_start_date?->format('Y-m-d') ?? 'N/A',
                    'trip_end_date' => $record->trip_end_date?->format('Y-m-d') ?? 'N/A',
                    'reason_for_allowances' => $record->reason_for_allowances,
                    'calculated_subtotal' => $record->calculated_subtotal ?? 0,
                    'status' => $record->status,
                    'uuid' => $record->uuid,
                ];

                Notification::send($users, new TsAllowanceAlert(collect($array)));
            }

            // Notify applicant
            $updateData = [
                'step' => $workflowParameter->name ?? $workflowParameter->status,
                'status' => 'APPROVED',
                'comment' => $data['comment'] ?? '',
            ];
            $record->applicant->notify(new TsAllowanceUpdate($updateData));

            return ['status' => 'success', 'message' => 'T&S Allowance Approved Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function reject($id, $data)
    {
        try {
            $record = $this->tsallowance
                ->with('workflow.workflowparameters.permission', 'applicant')
                ->where('id', $id)
                ->first();

            $workflowParameter = $record->workflow->workflowparameters
                ->where('status', $record->status)
                ->first();

            if (! $workflowParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            $record->status = 'REJECTED';
            $record->save();

            // Add comment
            $comments = $record->comments ?? collect();
            $comments->push([
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'comment' => $data['comment'],
                'created_at' => now()->toDateTimeString(),
            ]);
            $record->comments = $comments;
            $record->save();

            // Create approval record
            $this->tsallowanceapproval->create([
                'ts_allowance_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'REJECTED',
                'comment' => $data['comment'],
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Notify applicant of rejection
            $updateData = [
                'step' => $workflowParameter->name ?? $workflowParameter->status,
                'status' => 'REJECTED',
                'comment' => $data['comment'],
            ];
            $record->applicant->notify(new TsAllowanceUpdate($updateData));

            return ['status' => 'success', 'message' => 'T&S Allowance Rejected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function sendback($id, $data)
    {
        try {
            $record = $this->tsallowance
                ->with('workflow.workflowparameters.permission', 'applicant')
                ->where('id', $id)
                ->first();

            $workflowParameter = $record->workflow->workflowparameters
                ->where('status', $record->status)
                ->first();

            if (! $workflowParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            // Reset status to DRAFT so applicant can make corrections
            $record->status = 'DRAFT';
            $record->save();

            // Add comment explaining the send back
            $comments = $record->comments ?? collect();
            $comments->push([
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'comment' => 'SENT BACK FOR CORRECTIONS: '.$data['comment'],
                'step' => $workflowParameter->name,
                'created_at' => now()->toDateTimeString(),
            ]);
            $record->comments = $comments;
            $record->save();

            // Create approval record with SEND_BACK status
            // Old approval records are kept for audit trail
            // The approve/recommend methods check for most recent approval status
            $this->tsallowanceapproval->create([
                'ts_allowance_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'SEND_BACK',
                'comment' => $data['comment'],
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Send notification to applicant
            $applicant = $record->applicant;
            if ($applicant) {
                $array = [
                    'application_number' => $record->application_number,
                    'uuid' => $record->uuid,
                ];
                $applicant->notify(new TsAllowanceSendBack(collect($array), $data['comment']));
            }

            return ['status' => 'success', 'message' => 'T&S Allowance sent back to applicant for corrections'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function verifyfinance($id, $data)
    {
        // This method is deprecated - payment execution is now handled by Payment Voucher module
        return ['status' => 'error', 'message' => 'Finance verification is no longer used. Payment is handled through Payment Voucher module.'];
    }

    public function processpayment($id, $data)
    {
        // This method is deprecated - payment execution is now handled by Payment Voucher module
        return ['status' => 'error', 'message' => 'Payment processing is no longer used. Payment is handled through Payment Voucher module.'];
    }
}
