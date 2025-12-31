<?php

namespace App\implementation\services;

use App\Interfaces\repositories\itsallowanceInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\itsallowanceService;
use Illuminate\Support\Facades\Auth;

class _tsallowanceService implements itsallowanceService
{
    protected $tsallowancerepo;

    protected $userrepo;

    public function __construct(itsallowanceInterface $tsallowancerepo, iuserInterface $userrepo)
    {
        $this->tsallowancerepo = $tsallowancerepo;
        $this->userrepo = $userrepo;
    }

    public function getallowances($year, $search = null)
    {
        return $this->tsallowancerepo->getallowances($year, $search);
    }

    public function getdeptallowances($year, $departmentId = null, $search = null)
    {
        return $this->tsallowancerepo->getdeptallowances($year, $departmentId, $search);
    }

    public function getalldeptallowances($year, $departmentId = null, $search = null)
    {
        return $this->tsallowancerepo->getalldeptallowances($year, $departmentId, $search);
    }

    public function getuserdepartmentid($useremail)
    {
        $user = $this->userrepo->getuserbyemail($useremail);

        return $user?->department?->department_id;
    }

    public function getallallowances($year)
    {
        return $this->tsallowancerepo->getallallowances($year);
    }

    public function getallowance($id)
    {
        return $this->tsallowancerepo->getallowance($id);
    }

    public function getallowancebyuuid($uuid)
    {
        return $this->tsallowancerepo->getallowancebyuuid($uuid);
    }

    public function getallowancesbyapplicant($userId, $year, $search = null)
    {
        // Ensure user can only see their own allowances unless they have permission
        if (Auth::user()->id != $userId && ! Auth::user()->can('tsa.view.all')) {
            return collect()->paginate(10);
        }

        return $this->tsallowancerepo->getallowancesbyapplicant($userId, $year, $search);
    }

    public function getallowancesbystatus($year, $status)
    {
        return $this->tsallowancerepo->getallowancesbystatus($year, $status);
    }

    public function getallowancesbyworkflowparameter($year)
    {
        return $this->tsallowancerepo->getallowancesbyworkflowparameter($year);
    }

    public function createallowance($data)
    {
        // Set applicant to current user if not provided
        if (! isset($data['applicant_user_id'])) {
            $data['applicant_user_id'] = Auth::user()->id;
        }

        // Validate that user can only create allowances for themselves unless they have permission
        if ($data['applicant_user_id'] != Auth::user()->id && ! Auth::user()->can('tsa.create')) {
            return ['status' => 'error', 'message' => 'Unauthorized to create allowance for this user'];
        }

        return $this->tsallowancerepo->createallowance($data);
    }

    public function updateallowance($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Only applicant can update their own draft allowances
        if ($allowance->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Unauthorized to update this allowance'];
        }

        return $this->tsallowancerepo->updateallowance($id, $data);
    }

    public function deleteallowance($id)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Only applicant can delete their own draft allowances
        if ($allowance->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Unauthorized to delete this allowance'];
        }

        return $this->tsallowancerepo->deleteallowance($id);
    }

    public function submitallowance($id)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Only applicant can submit their own allowances
        if ($allowance->applicant_user_id != Auth::user()->id) {
            return ['status' => 'error', 'message' => 'Unauthorized to submit this allowance'];
        }

        // Check permission
        if (! Auth::user()->can('tsa.submit')) {
            return ['status' => 'error', 'message' => 'You do not have permission to submit allowances'];
        }

        return $this->tsallowancerepo->submitallowance($id);
    }

    public function recommend($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if user has permission to recommend at current step
        $workflowParameter = $allowance->workflow->workflowparameters
            ->where('status', $allowance->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        $requiredPermission = $workflowParameter->permission->name;
        if (! Auth::user()->can($requiredPermission)) {
            return ['status' => 'error', 'message' => 'You do not have permission to recommend at this step'];
        }

        // Check authorization code
        if (! isset($data['authorization_code'])) {
            return ['status' => 'error', 'message' => 'Authorization code is required'];
        }

        return $this->tsallowancerepo->recommend($id, $data);
    }

    public function rejectrecommendation($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if user has permission to reject at current step
        $workflowParameter = $allowance->workflow->workflowparameters
            ->where('status', $allowance->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        $requiredPermission = $workflowParameter->permission->name;
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

        return $this->tsallowancerepo->rejectrecommendation($id, $data);
    }

    public function approve($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if user has permission to approve at current step
        $workflowParameter = $allowance->workflow->workflowparameters
            ->where('status', $allowance->status)
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

        return $this->tsallowancerepo->approve($id, $data);
    }

    public function reject($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if user has permission to reject at current step
        $workflowParameter = $allowance->workflow->workflowparameters
            ->where('status', $allowance->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        $requiredPermission = $workflowParameter->permission->name;
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

        return $this->tsallowancerepo->reject($id, $data);
    }

    public function sendback($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if user has permission at current step
        $workflowParameter = $allowance->workflow->workflowparameters
            ->where('status', $allowance->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        $requiredPermission = $workflowParameter->permission->name;
        if (! Auth::user()->can($requiredPermission)) {
            return ['status' => 'error', 'message' => 'You do not have permission to send back at this step'];
        }

        // Check authorization code
        if (! isset($data['authorization_code'])) {
            return ['status' => 'error', 'message' => 'Authorization code is required'];
        }

        // Comment is required for send back
        if (empty($data['comment'])) {
            return ['status' => 'error', 'message' => 'Comment is required when sending back for corrections'];
        }

        return $this->tsallowancerepo->sendback($id, $data);
    }

    public function verifyfinance($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if allowance is approved
        if ($allowance->status != 'APPROVED') {
            return ['status' => 'error', 'message' => 'Finance verification can only be done for approved allowances'];
        }

        // Check permission
        if (! Auth::user()->can('tsa.verify.rates')) {
            return ['status' => 'error', 'message' => 'You do not have permission to verify finance'];
        }

        // Validate required fields
        $requiredFields = ['verified_total_amount'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "Field {$field} is required"];
            }
        }

        return $this->tsallowancerepo->verifyfinance($id, $data);
    }

    public function processpayment($id, $data)
    {
        $allowance = $this->tsallowancerepo->getallowance($id);

        // Check if allowance is CEO approved (final approval status)
        if ($allowance->status != 'APPROVED') {
            return ['status' => 'error', 'message' => 'Payment can only be processed for CEO approved allowances'];
        }

        // Check permission
        if (! Auth::user()->can('tsa.payment.execute')) {
            return ['status' => 'error', 'message' => 'You do not have permission to process payments'];
        }

        // Validate required fields
        $requiredFields = ['amount_paid_usd', 'payment_method', 'payment_reference', 'payment_date'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "Field {$field} is required"];
            }
        }

        return $this->tsallowancerepo->processpayment($id, $data);
    }
}
