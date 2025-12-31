<?php

namespace App\implementation\services;

use App\Interfaces\repositories\istaffwelfareloanInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\istaffwelfareloanService;
use Illuminate\Support\Facades\Auth;

class _staffwelfareloanService implements istaffwelfareloanService
{
    protected $staffwelfareloanrepo;

    protected $userrepo;

    public function __construct(istaffwelfareloanInterface $staffwelfareloanrepo, iuserInterface $userrepo)
    {
        $this->staffwelfareloanrepo = $staffwelfareloanrepo;
        $this->userrepo = $userrepo;
    }

    public function getloans($year, $search = null)
    {
        return $this->staffwelfareloanrepo->getloans($year, $search);
    }

    public function getdeptloans($year, $departmentId = null, $search = null)
    {
        return $this->staffwelfareloanrepo->getdeptloans($year, $departmentId, $search);
    }

    public function getuserdepartmentid($useremail)
    {
        $user = $this->userrepo->getuserbyemail($useremail);

        return $user?->department?->department_id;
    }

    public function getallloans($year)
    {
        return $this->staffwelfareloanrepo->getallloans($year);
    }

    public function getloan($id)
    {
        return $this->staffwelfareloanrepo->getloan($id);
    }

    public function getloanbyuuid($uuid)
    {
        return $this->staffwelfareloanrepo->getloanbyuuid($uuid);
    }

    public function getloansbyapplicant($userId, $year, $search = null)
    {
        // Ensure user can only see their own loans unless they have permission
        if (Auth::user()->id != $userId && ! Auth::user()->can('swl.view.all')) {
            return collect()->paginate(10);
        }

        return $this->staffwelfareloanrepo->getloansbyapplicant($userId, $year, $search);
    }

    public function getloansbystatus($year, $status)
    {
        return $this->staffwelfareloanrepo->getloansbystatus($year, $status);
    }

    public function getloansbyworkflowparameter($year)
    {
        return $this->staffwelfareloanrepo->getloansbyworkflowparameter($year);
    }

    public function createloan($data)
    {
        // Set applicant to current user if not provided
        if (! isset($data['applicant_user_id'])) {
            $data['applicant_user_id'] = Auth::user()->id;
        }

        // Validate that user can only create loans for themselves unless they have permission
        if ($data['applicant_user_id'] != Auth::user()->id && ! Auth::user()->can('swl.create')) {
            return ['status' => 'error', 'message' => 'Unauthorized to create loan for this user'];
        }

        return $this->staffwelfareloanrepo->createloan($data);
    }

    public function updateloan($id, $data)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Only applicant can update their own draft loans
        if ($loan->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Unauthorized to update this loan'];
        }

        return $this->staffwelfareloanrepo->updateloan($id, $data);
    }

    public function deleteloan($id)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Only applicant can delete their own draft loans
        if ($loan->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Unauthorized to delete this loan'];
        }

        return $this->staffwelfareloanrepo->deleteloan($id);
    }

    public function submitloan($id)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Only applicant can submit their own loans
        if ($loan->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Unauthorized to submit this loan'];
        }

        // Check permission
        if (! Auth::user()->can('swl.submit')) {
            return ['status' => 'error', 'message' => 'You do not have permission to submit loans'];
        }

        return $this->staffwelfareloanrepo->submitloan($id);
    }

    public function approve($id, $data)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Check if user has permission to approve at current step
        $workflowParameter = $loan->workflow->workflowparameters
            ->where('status', $loan->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        $requiredPermission = $workflowParameter->permission->name;
        if (! Auth::user()->can($requiredPermission)) {
            return ['status' => 'error', 'message' => 'You do not have permission to approve at this step'];
        }

        // Check authorization code
        if (! isset($data['authorization_code'])) {
            return ['status' => 'error', 'message' => 'Authorization code is required'];
        }

        return $this->staffwelfareloanrepo->approve($id, $data);
    }

    public function reject($id, $data)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Check if user has permission to reject at current step
        $workflowParameter = $loan->workflow->workflowparameters
            ->where('status', $loan->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        $requiredPermission = $workflowParameter->permission->name;
        // Rejection permission is typically the same as approval permission
        if (! Auth::user()->can($requiredPermission)) {
            return ['status' => 'error', 'message' => 'You do not have permission to reject at this step'];
        }

        // Check authorization code
        if (! isset($data['authorization_code'])) {
            return ['status' => 'error', 'message' => 'Authorization code is required'];
        }

        // Comment is required for rejection
        if (empty($data['comment'])) {
            return ['status' => 'error', 'message' => 'Comment is required when rejecting'];
        }

        return $this->staffwelfareloanrepo->reject($id, $data);
    }

    public function capturehrdata($id, $data)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Check if loan is at HR review step
        if ($loan->status != 'HR_REVIEW') {
            return ['status' => 'error', 'message' => 'HR data can only be captured at HR review step'];
        }

        // Check permission
        if (! Auth::user()->can('swl.edit.hr.section')) {
            return ['status' => 'error', 'message' => 'You do not have permission to capture HR data'];
        }

        return $this->staffwelfareloanrepo->capturehrdata($id, $data);
    }

    public function executepayment($id, $data)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Check if loan is approved
        if ($loan->status != 'APPROVED') {
            return ['status' => 'error', 'message' => 'Payment can only be executed for approved loans'];
        }

        // Check permission
        if (! Auth::user()->can('swl.payment.execute')) {
            return ['status' => 'error', 'message' => 'You do not have permission to execute payments'];
        }

        // Validate required fields
        $requiredFields = ['amount_paid', 'payment_method', 'payment_reference', 'payment_date', 'proof_of_payment_path'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "Field {$field} is required"];
            }
        }

        return $this->staffwelfareloanrepo->executepayment($id, $data);
    }

    public function acknowledgedebt($id, $data)
    {
        $loan = $this->staffwelfareloanrepo->getloan($id);

        // Check if loan is awaiting acknowledgement
        if ($loan->status != 'AWAITING_ACKNOWLEDGEMENT') {
            return ['status' => 'error', 'message' => 'Loan is not awaiting acknowledgement'];
        }

        // Only applicant can acknowledge
        if ($loan->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Only the applicant can acknowledge debt'];
        }

        // Check permission
        if (! Auth::user()->can('swl.acknowledge.debt')) {
            return ['status' => 'error', 'message' => 'You do not have permission to acknowledge debt'];
        }

        // Validate acknowledgement statement
        if (empty($data['acknowledgement_statement'])) {
            return ['status' => 'error', 'message' => 'Acknowledgement statement is required'];
        }

        return $this->staffwelfareloanrepo->acknowledgedebt($id, $data);
    }

    public function getActiveConfig()
    {
        return $this->staffwelfareloanrepo->getActiveConfig();
    }

    public function getConfig($id)
    {
        return $this->staffwelfareloanrepo->getConfig($id);
    }

    public function createConfig($data)
    {
        // Check permission for loan configuration management
        if (! Auth::user()->can('swl.config.manage')) {
            return ['status' => 'error', 'message' => 'You do not have permission to manage loan configuration'];
        }

        return $this->staffwelfareloanrepo->createConfig($data);
    }

    public function updateConfig($id, $data)
    {
        // Check permission for loan configuration management
        if (! Auth::user()->can('swl.config.manage')) {
            return ['status' => 'error', 'message' => 'You do not have permission to manage loan configuration'];
        }

        return $this->staffwelfareloanrepo->updateConfig($id, $data);
    }

    public function calculateExistingLoanBalance($userId)
    {
        return $this->staffwelfareloanrepo->calculateExistingLoanBalance($userId);
    }

    public function calculateLoanRepayment($principal, $interestRate, $months)
    {
        return $this->staffwelfareloanrepo->calculateLoanRepayment($principal, $interestRate, $months);
    }
}
