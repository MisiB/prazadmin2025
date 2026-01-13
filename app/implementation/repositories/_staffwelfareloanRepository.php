<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\istaffwelfareloanInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Models\Departmentuser;
use App\Models\StaffWelfareLoan;
use App\Models\StaffWelfareLoanApproval;
use App\Models\StaffWelfareLoanConfig;
use App\Models\StaffWelfareLoanPayment;
use App\Models\User;
use App\Models\Workflow;
use App\Notifications\StaffWelfareLoanAlert;
use App\Notifications\StaffWelfareLoanCompleted;
use App\Notifications\StaffWelfareLoanNotification;
use App\Notifications\StaffWelfareLoanUpdate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class _staffwelfareloanRepository implements istaffwelfareloanInterface
{
    protected $staffwelfareloan;

    protected $staffwelfareloanapproval;

    protected $staffwelfareloanpayment;

    protected $staffwelfareloanconfig;

    protected $workflow;

    protected $departmentuser;

    protected $userrepo;

    protected $departmentrepo;

    public function __construct(
        StaffWelfareLoan $staffwelfareloan,
        StaffWelfareLoanApproval $staffwelfareloanapproval,
        StaffWelfareLoanPayment $staffwelfareloanpayment,
        StaffWelfareLoanConfig $staffwelfareloanconfig,
        Workflow $workflow,
        Departmentuser $departmentuser,
        iuserInterface $userrepo,
        idepartmentInterface $departmentrepo
    ) {
        $this->staffwelfareloan = $staffwelfareloan;
        $this->staffwelfareloanapproval = $staffwelfareloanapproval;
        $this->staffwelfareloanpayment = $staffwelfareloanpayment;
        $this->staffwelfareloanconfig = $staffwelfareloanconfig;
        $this->workflow = $workflow;
        $this->departmentuser = $departmentuser;
        $this->userrepo = $userrepo;
        $this->departmentrepo = $departmentrepo;
    }

    public function getloans($year, $search = null)
    {
        $query = $this->staffwelfareloan
            ->with('workflow.workflowparameters.permission', 'department', 'applicant')
            ->whereNotIn('status', ['DRAFT'])
            ->whereYear('created_at', $year);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('loan_purpose', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getdeptloans($year, $departmentId = null, $search = null)
    {
        $query = $this->staffwelfareloan
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
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('loan_purpose', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function getallloans($year)
    {
        return $this->staffwelfareloan
            ->with('workflow.workflowparameters.permission', 'department', 'applicant')
            ->whereNotIn('status', ['DRAFT'])
            ->whereYear('created_at', $year)
            ->get();
    }

    public function getloan($id)
    {
        return $this->staffwelfareloan
            ->with('workflow.workflowparameters.permission', 'department', 'applicant', 'financeOfficer', 'approvals.approver', 'approvals.workflowParameter', 'payments.financeOfficer')
            ->find($id);
    }

    public function getloanbyuuid($uuid)
    {
        return $this->staffwelfareloan
            ->with('workflow.workflowparameters', 'department', 'applicant', 'financeOfficer', 'approvals.approver', 'approvals.workflowParameter', 'payments.financeOfficer')
            ->where('uuid', $uuid)
            ->first();
    }

    public function getloansbyapplicant($userId, $year, $search = null)
    {
        $query = $this->staffwelfareloan
            ->with('workflow.workflowparameters.permission', 'department')
            ->where('applicant_user_id', $userId)
            ->whereYear('created_at', $year);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('loan_purpose', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getloansbystatus($year, $status)
    {
        return $this->staffwelfareloan
            ->with('workflow.workflowparameters.permission', 'department', 'applicant', 'approvals.approver')
            ->whereYear('created_at', $year)
            ->where('status', $status)
            ->paginate(10);
    }

    public function getloansbyworkflowparameter($year)
    {
        $data = $this->workflow
            ->with([
                'workflowparameters.permission',
                'workflowparameters.staffwelfareloanapprovals' => function ($query) use ($year) {
                    $query->whereYear('created_at', $year)
                        ->whereNotIn('status', ['DRAFT', 'REJECTED']);
                },
            ])
            ->where('name', 'staff_welfare_loans')
            ->first();

        return $data?->workflowparameters ?? collect();
    }

    public function createloan($data)
    {
        try {
            $workflowname = config('workflow.staff_welfare_loans');
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

            $loanNumber = 'SWL'.date('Y').random_int(1000, 9999999);

            $data['uuid'] = Str::uuid()->toString();
            $data['workflow_id'] = $workflow->id;
            $data['loan_number'] = $loanNumber;
            $data['department_id'] = $departmentuser->department_id;
            $data['full_name'] = $user->name;
            $data['status'] = 'DRAFT';
            $data['applicant_user_id'] = $data['applicant_user_id'];

            $this->staffwelfareloan->create($data);

            return ['status' => 'success', 'message' => 'Staff Welfare Loan Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateloan($id, $data)
    {
        try {
            $record = $this->staffwelfareloan->find($id);

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Loan Cannot be Modified After Submission'];
            }

            // Only allow updating applicant section fields in draft
            $allowedFields = [
                'employee_number',
                'job_title',
                'date_joined',
                'loan_amount_requested',
                'loan_purpose',
                'repayment_period_months',
            ];

            $updateData = array_intersect_key($data, array_flip($allowedFields));
            $record->update($updateData);

            return ['status' => 'success', 'message' => 'Staff Welfare Loan Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteloan($id)
    {
        try {
            $record = $this->staffwelfareloan->where('id', $id)->first();

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Loan Cannot be Deleted After Submission'];
            }

            $record->delete();

            return ['status' => 'success', 'message' => 'Staff Welfare Loan Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitloan($id)
    {
        try {
            $record = $this->staffwelfareloan->with('workflow.workflowparameters')->where('id', $id)->first();

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Loan Already Submitted'];
            }

            // Validate required fields
            $requiredFields = [
                'employee_number',
                'job_title',
                'date_joined',
                'loan_amount_requested',
                'loan_purpose',
                'repayment_period_months',
            ];

            foreach ($requiredFields as $field) {
                if (empty($record->$field)) {
                    return ['status' => 'error', 'message' => "Field {$field} is required"];
                }
            }

            // Get first workflow parameter (Supervisor step)
            $firstWorkflowParameter = $record->workflow->workflowparameters->where('order', 1)->first();

            if (! $firstWorkflowParameter) {
                return ['status' => 'error', 'message' => 'Workflow Configuration Error'];
            }

            $record->status = $firstWorkflowParameter->status; // Should be "SUBMITTED"
            $record->applicant_digital_declaration = true;
            $record->submission_date = now();
            $record->save();

            // Get supervisor (HOD) for the department
            $departmentuser = $this->departmentuser->with('supervisor')
                ->where('user_id', $record->applicant_user_id)
                ->first();

            if ($departmentuser && $departmentuser->supervisor) {
                $users = User::permission($firstWorkflowParameter->permission->name)->get();

                if ($users->count() > 0) {
                    $array = [];
                    $array['loan_number'] = $record->loan_number;
                    $array['full_name'] = $record->full_name;
                    $array['department'] = $record->department->name ?? '';
                    $array['loan_amount_requested'] = $record->loan_amount_requested;
                    $array['loan_purpose'] = $record->loan_purpose;
                    $array['uuid'] = $record->uuid;
                    $array['status'] = $record->status;

                    Notification::send($users, new StaffWelfareLoanAlert(collect($array)));
                }
            }

            return ['status' => 'success', 'message' => 'Staff Welfare Loan Submitted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approve($id, $data)
    {
        try {
            $record = $this->staffwelfareloan
                ->with('workflow.workflowparameters.permission', 'applicant')
                ->where('id', $id)
                ->first();

            $workflowParameter = $record->workflow->workflowparameters
                ->where('status', $record->status)
                ->first();

            if (! $workflowParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            // Check if already approved at this step
            $existingApproval = $this->staffwelfareloanapproval
                ->where('staff_welfare_loan_id', $id)
                ->where('workflowparameter_id', $workflowParameter->id)
                ->first();

            if ($existingApproval) {
                return ['status' => 'error', 'message' => 'Already Processed at This Step'];
            }

            // Validate HR data has been captured before HR can approve
            if ($record->status === 'HR_REVIEW' && ! $record->hr_digital_confirmation) {
                return ['status' => 'error', 'message' => 'HR Data Must Be Captured Before Approval'];
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

            $record->save();

            // Create approval record
            $this->staffwelfareloanapproval->create([
                'staff_welfare_loan_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'APPROVED',
                'comment' => $data['comment'] ?? '',
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Send notifications
            if ($users->count() > 0) {
                $array = [];
                $array['loan_number'] = $record->loan_number;
                $array['full_name'] = $record->full_name;
                $array['department'] = $record->department->name ?? '';
                $array['loan_amount_requested'] = $record->loan_amount_requested;
                $array['loan_purpose'] = $record->loan_purpose;
                $array['uuid'] = $record->uuid;
                $array['status'] = $record->status;

                Notification::send($users, new StaffWelfareLoanNotification(collect($array)));
            }

            // Notify applicant
            $array2 = [];
            $array2['step'] = $workflowParameter->status;
            $array2['status'] = 'APPROVED';
            $array2['comment'] = $data['comment'] ?? '';
            $record->applicant->notify(new StaffWelfareLoanUpdate($array2));

            return ['status' => 'success', 'message' => 'Staff Welfare Loan Approved Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function reject($id, $data)
    {
        try {
            $record = $this->staffwelfareloan
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
            $this->staffwelfareloanapproval->create([
                'staff_welfare_loan_id' => $record->id,
                'workflowparameter_id' => $workflowParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'REJECTED',
                'comment' => $data['comment'],
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => true,
                'approved_at' => now(),
            ]);

            // Notify applicant
            $array2 = [];
            $array2['step'] = $workflowParameter->status;
            $array2['status'] = 'REJECTED';
            $array2['comment'] = $data['comment'];
            $record->applicant->notify(new StaffWelfareLoanUpdate($array2));

            return ['status' => 'success', 'message' => 'Staff Welfare Loan Rejected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function capturehrdata($id, $data)
    {
        try {
            $record = $this->staffwelfareloan->where('id', $id)->first();

            if ($record->status != 'HR_REVIEW') {
                return ['status' => 'error', 'message' => 'HR Data Can Only be Captured at HR Review Step'];
            }

            // Get active loan config for interest rate
            $config = $this->getActiveConfig();
            $interestRate = $config ? (float) $config->interest_rate : 0;

            // Calculate existing loan balance from all active loans
            $existingLoanBalance = $this->calculateExistingLoanBalance($record->applicant_user_id);

            // Calculate loan repayment with interest
            $loanAmount = (float) $record->loan_amount_requested;
            $repaymentMonths = (int) $record->repayment_period_months;

            $repaymentDetails = $this->calculateLoanRepayment($loanAmount, $interestRate, $repaymentMonths);

            // Hash the salary for privacy (store both plain and hashed)
            $basicSalary = isset($data['basic_salary']) ? (float) $data['basic_salary'] : null;
            $salaryHash = $basicSalary ? Hash::make((string) $basicSalary) : null;

            // Auto-calculate last payment date based on repayment period
            // Use date of engagement if provided, otherwise use today's date as the start
            $startDate = isset($data['date_of_engagement']) && $data['date_of_engagement']
                ? Carbon::parse($data['date_of_engagement'])
                : now();
            $lastPaymentDate = $startDate->copy()->addMonths($repaymentMonths);

            // Update HR section fields
            $updateData = [
                'employment_status' => $data['employment_status'] ?? null,
                'date_of_engagement' => $data['date_of_engagement'] ?? null,
                'basic_salary' => $basicSalary,
                'basic_salary_hash' => $salaryHash,
                'existing_loan_balance' => $existingLoanBalance,
                'interest_rate_applied' => $interestRate,
                'interest_amount' => $repaymentDetails['interest_amount'],
                'total_repayment_amount' => $repaymentDetails['total_repayment'],
                'monthly_deduction_amount' => $repaymentDetails['monthly_deduction'],
                'monthly_repayment' => $repaymentDetails['monthly_deduction'],
                'last_payment_date' => $lastPaymentDate,
                'hr_comments' => $data['hr_comments'] ?? null,
                'hr_digital_confirmation' => true,
                'hr_review_date' => now(),
            ];

            $record->update($updateData);

            return ['status' => 'success', 'message' => 'HR Data Captured Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function executepayment($id, $data)
    {
        // This method is deprecated - payment execution is now handled by Payment Voucher module
        return ['status' => 'error', 'message' => 'Payment execution is no longer used. Payment is handled through Payment Voucher module.'];
    }

    public function acknowledgedebt($id, $data)
    {
        try {
            $record = $this->staffwelfareloan->with('department', 'applicant')->where('id', $id)->first();

            if ($record->status != 'AWAITING_ACKNOWLEDGEMENT') {
                return ['status' => 'error', 'message' => 'Loan is Not Awaiting Acknowledgement'];
            }

            if ($record->applicant_user_id != Auth::user()->id) {
                return ['status' => 'error', 'message' => 'Only the Applicant Can Acknowledge Debt'];
            }

            $record->acknowledgement_of_debt_statement = $data['acknowledgement_statement'];
            $record->employee_digital_acceptance = true;
            $record->acceptance_date = now();
            $record->status = 'COMPLETED';
            $record->save();

            // Notify HR users with approval and HR queue view permissions that loan is completed
            $hrUsers = User::permission(['swl.approve.hr', 'swl.view.hr.queue'])->get();

            if ($hrUsers->count() > 0) {
                $array = [];
                $array['loan_number'] = $record->loan_number;
                $array['full_name'] = $record->full_name;
                $array['employee_number'] = $record->employee_number;
                $array['department'] = $record->department->name ?? 'N/A';
                $array['amount_paid'] = $record->amount_paid;
                $array['payment_date'] = $record->payment_date?->format('Y-m-d') ?? 'N/A';
                $array['acceptance_date'] = $record->acceptance_date->format('Y-m-d H:i:s');
                $array['uuid'] = $record->uuid;

                Notification::send($hrUsers, new StaffWelfareLoanCompleted(collect($array)));
            }

            return ['status' => 'success', 'message' => 'Debt Acknowledged Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getActiveConfig()
    {
        return $this->staffwelfareloanconfig->where('is_active', true)->first();
    }

    public function getConfig($id)
    {
        return $this->staffwelfareloanconfig->find($id);
    }

    public function createConfig($data)
    {
        try {
            // Deactivate any existing active config
            $this->staffwelfareloanconfig->where('is_active', true)->update(['is_active' => false]);

            $data['created_by'] = Auth::user()->id;
            $data['is_active'] = true;

            $this->staffwelfareloanconfig->create($data);

            return ['status' => 'success', 'message' => 'Loan Configuration Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateConfig($id, $data)
    {
        try {
            $config = $this->staffwelfareloanconfig->find($id);

            if (! $config) {
                return ['status' => 'error', 'message' => 'Configuration Not Found'];
            }

            $data['updated_by'] = Auth::user()->id;
            $config->update($data);

            return ['status' => 'success', 'message' => 'Loan Configuration Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate existing loan balance from all active loans for a user
     * Active loans are those with status COMPLETED but not fully repaid,
     * or in AWAITING_ACKNOWLEDGEMENT status
     */
    public function calculateExistingLoanBalance($userId): float
    {
        // Get all loans that are still being repaid (COMPLETED status means loan was disbursed)
        // Exclude DRAFT, REJECTED, and CANCELLED loans
        $activeLoans = $this->staffwelfareloan
            ->where('applicant_user_id', $userId)
            ->whereIn('status', ['COMPLETED', 'AWAITING_ACKNOWLEDGEMENT', 'APPROVED'])
            ->whereNotNull('amount_paid')
            ->get();

        $totalBalance = 0;

        foreach ($activeLoans as $loan) {
            // Use total_repayment_amount if available (includes interest), otherwise use amount_paid
            $loanTotal = $loan->total_repayment_amount ?? $loan->amount_paid ?? 0;
            $totalBalance += (float) $loanTotal;
        }

        return round($totalBalance, 2);
    }

    /**
     * Calculate loan repayment details including interest
     *
     * @param  float  $principal  The loan amount
     * @param  float  $annualInterestRate  Annual interest rate as percentage (e.g., 10 for 10%)
     * @param  int  $months  Repayment period in months
     * @return array Repayment details
     */
    public function calculateLoanRepayment($principal, $annualInterestRate, $months): array
    {
        $principal = (float) $principal;
        $annualInterestRate = (float) $annualInterestRate;
        $months = (int) $months;

        // Simple interest calculation: Interest = Principal * Rate * Time
        // Where Time is in years (months / 12)
        $timeInYears = $months / 12;
        $interestAmount = $principal * ($annualInterestRate / 100) * $timeInYears;

        $totalRepayment = $principal + $interestAmount;
        $monthlyDeduction = $months > 0 ? $totalRepayment / $months : 0;

        return [
            'principal' => round($principal, 2),
            'interest_rate' => round($annualInterestRate, 2),
            'interest_amount' => round($interestAmount, 2),
            'total_repayment' => round($totalRepayment, 2),
            'monthly_deduction' => round($monthlyDeduction, 2),
            'repayment_months' => $months,
        ];
    }
}
