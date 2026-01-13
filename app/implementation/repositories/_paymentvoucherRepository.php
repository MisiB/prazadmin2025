<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ipaymentvoucherInterface;
use App\Models\PaymentRequisition;
use App\Models\PaymentRequisitionLineItem;
use App\Models\Paymentvoucher;
use App\Models\Paymentvoucherapproval;
use App\Models\Paymentvoucherauditlog;
use App\Models\Paymentvoucherconfig;
use App\Models\Paymentvoucheritem;
use App\Models\StaffWelfareLoan;
use App\Models\TsAllowance;
use App\Models\Workflow;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class _paymentvoucherRepository implements ipaymentvoucherInterface
{
    protected $paymentvoucher;

    protected $paymentvoucheritem;

    protected $paymentvoucherconfig;

    protected $paymentvoucherauditlog;

    protected $paymentrequisition;

    protected $paymentrequisitionlineitem;

    protected $tsallowance;

    protected $staffwelfareloan;

    protected $workflow;

    protected $paymentvoucherapproval;

    public function __construct(
        PaymentVoucher $paymentvoucher,
        PaymentVoucherItem $paymentvoucheritem,
        PaymentVoucherConfig $paymentvoucherconfig,
        PaymentVoucherAuditLog $paymentvoucherauditlog,
        PaymentRequisition $paymentrequisition,
        PaymentRequisitionLineItem $paymentrequisitionlineitem,
        TsAllowance $tsallowance,
        StaffWelfareLoan $staffwelfareloan,
        Workflow $workflow,
        PaymentVoucherApproval $paymentvoucherapproval
    ) {
        $this->paymentvoucher = $paymentvoucher;
        $this->paymentvoucheritem = $paymentvoucheritem;
        $this->paymentvoucherconfig = $paymentvoucherconfig;
        $this->paymentvoucherauditlog = $paymentvoucherauditlog;
        $this->paymentrequisition = $paymentrequisition;
        $this->paymentrequisitionlineitem = $paymentrequisitionlineitem;
        $this->tsallowance = $tsallowance;
        $this->staffwelfareloan = $staffwelfareloan;
        $this->workflow = $workflow;
        $this->paymentvoucherapproval = $paymentvoucherapproval;
    }

    public function getvouchers($year, $search = null)
    {
        $query = $this->paymentvoucher->with('preparedBy', 'items', 'workflow.workflowparameters.permission')
            ->whereYear('voucher_date', $year);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getvoucher($id)
    {
        return $this->paymentvoucher->with([
            'preparedBy',
            'verifiedBy',
            'checkedBy',
            'financeApprovedBy',
            'ceoApprovedBy',
            'items',
            'auditLogs.user',
        ])->find($id);
    }

    public function getvoucherbyuuid($uuid)
    {
        return $this->paymentvoucher->with([
            'preparedBy',
            'verifiedBy',
            'checkedBy',
            'financeApprovedBy',
            'ceoApprovedBy',
            'bankAccount.currency',
            'items',
            'auditLogs.user',
        ])->where('uuid', $uuid)->first();
    }

    public function getvoucherbystatus($year, $status)
    {
        return $this->paymentvoucher
            ->with('preparedBy', 'items', 'workflow.workflowparameters.permission')
            ->whereYear('voucher_date', $year)
            ->where('status', $status)
            ->get();
    }

    public function getallvouchers($year)
    {
        return $this->paymentvoucher
            ->with('preparedBy', 'items', 'workflow.workflowparameters.permission')
            ->whereYear('voucher_date', $year)
            ->whereNotIn('status', ['DRAFT', 'REJECTED'])
            ->get();
    }

    public function geteligibleitems($year)
    {
        $items = collect();

        // Get Payment Requisitions with AWAITING_PAYMENT status
        $paymentRequisitions = $this->paymentrequisition
            ->with('lineItems', 'currency', 'department')
            ->where('year', $year)
            ->where('status', 'AWAITING_PAYMENT')
            ->get();

        foreach ($paymentRequisitions as $pr) {
            foreach ($pr->lineItems as $lineItem) {
                // Check if this line item is already on a voucher
                $exists = $this->paymentvoucheritem
                    ->where('source_type', 'PAYMENT_REQUISITION')
                    ->where('source_id', $pr->id)
                    ->where('source_line_id', $lineItem->id)
                    ->exists();

                if (! $exists) {
                    $items->push([
                        'source_type' => 'PAYMENT_REQUISITION',
                        'source_id' => $pr->id,
                        'source_line_id' => $lineItem->id,
                        'description' => $pr->purpose.' - '.$lineItem->description,
                        'original_currency' => $pr->currency->name ?? 'USD',
                        'original_amount' => $lineItem->line_total,
                        'reference' => $pr->reference_number,
                        'department' => $pr->department->name ?? '',
                    ]);
                }
            }
        }

        // Get T&S Allowances with AWAITING_PAYMENT status
        $tsAllowances = $this->tsallowance
            ->with('department', 'applicant')
            ->whereYear('trip_start_date', $year)
            ->where('status', 'AWAITING_PAYMENT')
            ->get();

        foreach ($tsAllowances as $ts) {
            // Check if already on a voucher
            $exists = $this->paymentvoucheritem
                ->where('source_type', 'TNS')
                ->where('source_id', $ts->id)
                ->whereNull('source_line_id')
                ->exists();

            if (! $exists) {
                $applicantName = $ts->full_name ?? ($ts->applicant->name ?? 'N/A');
                $description = 'T&S: '.$ts->reason_for_allowances.' - '.$applicantName.' ('.$ts->trip_start_date->format('Y-m-d').' to '.$ts->trip_end_date->format('Y-m-d').')';
                $items->push([
                    'source_type' => 'TNS',
                    'source_id' => $ts->id,
                    'source_line_id' => null,
                    'description' => $description,
                    'original_currency' => 'USD', // T&S typically in USD
                    'original_amount' => $ts->balance_due ?? 0,
                    'reference' => $ts->application_number,
                    'department' => $ts->department->name ?? '',
                ]);
            }
        }

        // Get Staff Welfare Loans with AWAITING_PAYMENT status
        $staffWelfareLoans = $this->staffwelfareloan
            ->with('department', 'applicant')
            ->whereYear('created_at', $year)
            ->where('status', 'AWAITING_PAYMENT')
            ->get();

        foreach ($staffWelfareLoans as $swl) {
            // Check if already on a voucher
            $exists = $this->paymentvoucheritem
                ->where('source_type', 'STAFF_WELFARE')
                ->where('source_id', $swl->id)
                ->whereNull('source_line_id')
                ->exists();

            if (! $exists) {
                $applicantName = $swl->applicant->name ?? 'N/A';
                $description = 'Staff Welfare Loan #'.$swl->loan_number.': '.$swl->loan_purpose.' - '.$applicantName;
                $items->push([
                    'source_type' => 'STAFF_WELFARE',
                    'source_id' => $swl->id,
                    'source_line_id' => null,
                    'description' => $description,
                    'original_currency' => 'USD', // Staff Welfare typically in USD
                    'original_amount' => $swl->loan_amount_requested,
                    'reference' => $swl->loan_number,
                    'department' => $swl->department->name ?? '',
                ]);
            }
        }

        return $items;
    }

    public function createvoucher($data)
    {
        try {
            // Get workflow
            $workflowName = config('workflow.payment_vouchers', 'payment_vouchers');
            $workflow = $this->workflow->where('name', $workflowName)->first();

            if (! $workflow) {
                return ['status' => 'error', 'message' => 'Payment Voucher Workflow Not Configured'];
            }

            // Generate voucher number
            $voucherNumber = 'PV'.date('Y').random_int(1000, 9999999);

            // Create voucher
            $voucher = $this->paymentvoucher->create([
                'uuid' => Str::uuid()->toString(),
                'voucher_number' => $voucherNumber,
                'voucher_date' => $data['voucher_date'] ?? now()->toDateString(),
                'currency' => $data['currency'],
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'exchange_rate' => $data['exchange_rate'] ?? null,
                'total_amount' => 0, // Will be calculated
                'status' => 'DRAFT',
                'prepared_by' => Auth::user()->id,
                'workflow_id' => $workflow->id,
            ]);

            // Add items
            $totalAmount = 0;
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    // Use edited amount if provided, otherwise use original amount
                    $itemAmount = $itemData['edited_amount'] ?? $itemData['original_amount'];

                    $payableAmount = $itemAmount;
                    // Only apply exchange rate if currency is ZiG and exchange rate is provided
                    // and the item currency is different from voucher currency
                    if ($data['currency'] === 'ZiG' && isset($data['exchange_rate']) && $itemData['original_currency'] !== 'ZiG') {
                        $payableAmount = $itemAmount * $data['exchange_rate'];
                    }

                    $this->paymentvoucheritem->create([
                        'payment_voucher_id' => $voucher->id,
                        'source_type' => $itemData['source_type'],
                        'source_id' => $itemData['source_id'],
                        'source_line_id' => $itemData['source_line_id'] ?? null,
                        'description' => $itemData['description'],
                        'original_currency' => $itemData['original_currency'],
                        'original_amount' => $itemData['original_amount'],
                        'edited_amount' => $itemData['edited_amount'] ?? null,
                        'amount_change_comment' => $itemData['amount_change_comment'] ?? null,
                        'account_type' => $itemData['account_type'] ?? null,
                        'gl_code' => $itemData['gl_code'] ?? null,
                        'exchange_rate' => $data['exchange_rate'] ?? null,
                        'payable_amount' => $payableAmount,
                    ]);

                    $totalAmount += $payableAmount;

                    // Update source status to ON_VOUCHER
                    $this->updatesourcestatus($itemData['source_type'], $itemData['source_id'], $itemData['source_line_id'] ?? null, 'ON_VOUCHER');
                }
            }

            // Update total amount
            $voucher->total_amount = $totalAmount;
            $voucher->save();

            // Create audit log
            $this->createauditlog($voucher->id, 'CREATE', null, 'DRAFT');

            return ['status' => 'success', 'message' => 'Payment Voucher Created Successfully', 'data' => $voucher];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updatevoucher($id, $data)
    {
        try {
            $voucher = $this->paymentvoucher->find($id);

            if (! $voucher) {
                return ['status' => 'error', 'message' => 'Payment Voucher Not Found'];
            }

            if ($voucher->status !== 'DRAFT') {
                return ['status' => 'error', 'message' => 'Voucher Cannot Be Modified After Submission'];
            }

            // Update voucher fields
            if (isset($data['voucher_date'])) {
                $voucher->voucher_date = $data['voucher_date'];
            }
            if (isset($data['currency'])) {
                $voucher->currency = $data['currency'];
            }
            if (isset($data['bank_account_id'])) {
                $voucher->bank_account_id = $data['bank_account_id'];
            }
            if (isset($data['exchange_rate'])) {
                $voucher->exchange_rate = $data['exchange_rate'];
            }

            // Update items if provided
            if (isset($data['items'])) {
                // Remove existing items
                $this->paymentvoucheritem->where('payment_voucher_id', $voucher->id)->delete();

                // Add new items
                $totalAmount = 0;
                foreach ($data['items'] as $itemData) {
                    // Use edited amount if provided, otherwise use original amount
                    $itemAmount = $itemData['edited_amount'] ?? $itemData['original_amount'];

                    $payableAmount = $itemAmount;
                    // Only apply exchange rate if currency is ZiG and exchange rate is provided
                    // and the item currency is different from voucher currency
                    if ($voucher->currency === 'ZiG' && $voucher->exchange_rate && $itemData['original_currency'] !== 'ZiG') {
                        $payableAmount = $itemAmount * $voucher->exchange_rate;
                    }

                    $this->paymentvoucheritem->create([
                        'payment_voucher_id' => $voucher->id,
                        'source_type' => $itemData['source_type'],
                        'source_id' => $itemData['source_id'],
                        'source_line_id' => $itemData['source_line_id'] ?? null,
                        'description' => $itemData['description'],
                        'original_currency' => $itemData['original_currency'],
                        'original_amount' => $itemData['original_amount'],
                        'edited_amount' => $itemData['edited_amount'] ?? null,
                        'amount_change_comment' => $itemData['amount_change_comment'] ?? null,
                        'account_type' => $itemData['account_type'] ?? null,
                        'gl_code' => $itemData['gl_code'] ?? null,
                        'exchange_rate' => $voucher->exchange_rate,
                        'payable_amount' => $payableAmount,
                    ]);

                    $totalAmount += $payableAmount;
                }

                $voucher->total_amount = $totalAmount;
            }

            $voucher->save();

            return ['status' => 'success', 'message' => 'Payment Voucher Updated Successfully', 'data' => $voucher];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletevoucher($id)
    {
        try {
            $voucher = $this->paymentvoucher->find($id);

            if (! $voucher) {
                return ['status' => 'error', 'message' => 'Payment Voucher Not Found'];
            }

            if ($voucher->status !== 'DRAFT') {
                return ['status' => 'error', 'message' => 'Only Draft Vouchers Can Be Deleted'];
            }

            // Release source items
            foreach ($voucher->items as $item) {
                $this->updatesourcestatus($item->source_type, $item->source_id, $item->source_line_id, 'AWAITING_PAYMENT');
            }

            $voucher->delete();

            return ['status' => 'success', 'message' => 'Payment Voucher Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitvoucher($id)
    {
        try {
            $voucher = $this->paymentvoucher->with('items', 'workflow')->where('id', $id)->first();

            if (! $voucher) {
                return ['status' => 'error', 'message' => 'Payment Voucher Not Found'];
            }

            if ($voucher->status !== 'DRAFT') {
                return ['status' => 'error', 'message' => 'Payment Voucher Already Submitted'];
            }

            // Validate required fields
            if (empty($voucher->voucher_date)) {
                return ['status' => 'error', 'message' => 'Voucher Date is required'];
            }

            if (empty($voucher->currency)) {
                return ['status' => 'error', 'message' => 'Currency is required'];
            }

            // Validate exchange rate for ZiG
            if ($voucher->currency === 'ZiG' && empty($voucher->exchange_rate)) {
                return ['status' => 'error', 'message' => 'Exchange rate is required for ZiG currency'];
            }

            // Check if items exist
            if ($voucher->items()->count() === 0) {
                return ['status' => 'error', 'message' => 'At least one item is required'];
            }

            // Validate total amount
            if ($voucher->total_amount <= 0) {
                return ['status' => 'error', 'message' => 'Total amount must be greater than zero'];
            }

            // Change status to SUBMITTED
            $voucher->status = 'SUBMITTED';
            $voucher->save();

            // Create audit log
            $this->createauditlog($voucher->id, 'SUBMIT', 'DRAFT', 'SUBMITTED');

            return ['status' => 'success', 'message' => 'Payment Voucher Submitted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approve($id, $data)
    {
        try {
            $voucher = $this->paymentvoucher
                ->with('workflow.workflowparameters.permission', 'items')
                ->where('id', $id)
                ->first();

            if (! $voucher) {
                return ['status' => 'error', 'message' => 'Payment Voucher Not Found'];
            }

            if (! $voucher->workflow) {
                return ['status' => 'error', 'message' => 'Workflow Not Configured for This Voucher'];
            }

            $oldStatus = $voucher->status;
            $workflowParameters = $voucher->workflow->workflowparameters->sortBy('order');

            // Find current workflow parameter based on voucher status
            $currentParameter = $workflowParameters
                ->where('status', $voucher->status)
                ->first();

            if (! $currentParameter) {
                return ['status' => 'error', 'message' => 'Invalid Workflow Step'];
            }

            // Check if already approved at this step
            $existingApproval = $this->paymentvoucherapproval
                ->where('payment_voucher_id', $id)
                ->where('workflowparameter_id', $currentParameter->id)
                ->first();

            if ($existingApproval && $existingApproval->status === 'APPROVED') {
                return ['status' => 'error', 'message' => 'Already Processed at This Step'];
            }

            // Set approver field based on current parameter's name/role
            // The status field indicates what vouchers this stage handles
            // Verified By handles SUBMITTED vouchers
            // Checked By handles VERIFIED vouchers
            // etc.
            if ($currentParameter->status === 'SUBMITTED' || stripos($currentParameter->name ?? '', 'Verified') !== false) {
                $voucher->verified_by = Auth::user()->id;
            } elseif ($currentParameter->status === 'VERIFIED' || stripos($currentParameter->name ?? '', 'Checked') !== false) {
                $voucher->checked_by = Auth::user()->id;
            } elseif ($currentParameter->status === 'CHECKED' || stripos($currentParameter->name ?? '', 'Finance') !== false) {
                $voucher->finance_approved_by = Auth::user()->id;
            } elseif ($currentParameter->status === 'FINANCE_RECOMMENDED' || stripos($currentParameter->name ?? '', 'CEO') !== false) {
                $voucher->ceo_approved_by = Auth::user()->id;
            }

            // Move to next step - find next workflow parameter
            $nextParameter = $workflowParameters
                ->where('order', $currentParameter->order + 1)
                ->first();

            if ($nextParameter) {
                // Move to next workflow parameter status
                $voucher->status = $nextParameter->status;
            } else {
                // All approvals complete - set to CEO_APPROVED (final status)
                $voucher->status = 'CEO_APPROVED';

                // Mark all source items as PAID
                foreach ($voucher->items as $item) {
                    $this->updatesourcestatus($item->source_type, $item->source_id, $item->source_line_id, 'PAID');
                }
            }

            $voucher->save();

            // Create approval record
            $this->paymentvoucherapproval->create([
                'payment_voucher_id' => $voucher->id,
                'workflowparameter_id' => $currentParameter->id,
                'user_id' => Auth::user()->id,
                'status' => 'APPROVED',
                'comment' => $data['comment'] ?? null,
                'authorization_code_hash' => isset($data['authorization_code']) ? Hash::make($data['authorization_code']) : null,
                'authorization_code_validated' => isset($data['authorization_code']),
                'approved_at' => now(),
            ]);

            // Create audit log
            $this->createauditlog($voucher->id, 'APPROVE', $oldStatus, $voucher->status, $data['comment'] ?? null);

            return ['status' => 'success', 'message' => 'Payment Voucher Approved Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Keep old methods for backward compatibility (deprecated)
    public function verify($id, $data)
    {
        return $this->approve($id, $data);
    }

    public function check($id, $data)
    {
        return $this->approve($id, $data);
    }

    public function approverfinance($id, $data)
    {
        return $this->approve($id, $data);
    }

    public function approveceo($id, $data)
    {
        return $this->approve($id, $data);
    }

    public function reject($id, $data)
    {
        try {
            $voucher = $this->paymentvoucher->find($id);

            if (! $voucher) {
                return ['status' => 'error', 'message' => 'Payment Voucher Not Found'];
            }

            $oldStatus = $voucher->status;

            $voucher->status = 'REJECTED';
            $voucher->rejection_reason = $data['comment'] ?? '';
            $voucher->save();

            // Release source items back to AWAITING_PAYMENT
            foreach ($voucher->items as $item) {
                $this->updatesourcestatus($item->source_type, $item->source_id, $item->source_line_id, 'AWAITING_PAYMENT');
            }

            $this->createauditlog($voucher->id, 'REJECT', $oldStatus, 'REJECTED', $data['comment'] ?? null);

            return ['status' => 'success', 'message' => 'Payment Voucher Rejected Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getconfig($key)
    {
        $config = $this->paymentvoucherconfig->where('config_key', $key)->first();

        return $config ? $config->config_value : null;
    }

    public function setconfig($key, $value, $description = null)
    {
        try {
            $config = $this->paymentvoucherconfig->where('config_key', $key)->first();

            if ($config) {
                $config->config_value = $value;
                if ($description) {
                    $config->description = $description;
                }
                $config->updated_by = Auth::user()->id;
                $config->save();
            } else {
                $this->paymentvoucherconfig->create([
                    'config_key' => $key,
                    'config_value' => $value,
                    'description' => $description,
                    'updated_by' => Auth::user()->id,
                ]);
            }

            return ['status' => 'success', 'message' => 'Configuration Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getallconfigs()
    {
        return $this->paymentvoucherconfig->all();
    }

    public function getvouchersbyworkflowparameter($year)
    {
        $data = $this->workflow
            ->with([
                'workflowparameters.permission',
                'workflowparameters.paymentvoucherapprovals' => function ($query) use ($year) {
                    $query->whereYear('created_at', $year)
                        ->whereNotIn('status', ['DRAFT', 'REJECTED']);
                },
            ])
            ->where('name', config('workflow.payment_vouchers', 'payment_vouchers'))
            ->first();

        return $data?->workflowparameters ?? collect();
    }

    public function createauditlog($voucherId, $action, $oldStatus, $newStatus, $comments = null)
    {
        try {
            $voucher = $this->paymentvoucher->find($voucherId);

            $this->paymentvoucherauditlog->create([
                'payment_voucher_id' => $voucherId,
                'action' => $action,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => Auth::user()->id,
                'role' => Auth::user()->roles->first()?->name ?? null,
                'comments' => $comments,
                'exchange_rate' => $voucher->exchange_rate ?? null,
                'timestamp' => now(),
            ]);

            return ['status' => 'success'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Update source record status
     */
    private function updatesourcestatus($sourceType, $sourceId, $sourceLineId, $status)
    {
        try {
            switch ($sourceType) {
                case 'PAYMENT_REQUISITION':
                    $record = $this->paymentrequisition->find($sourceId);
                    if ($record) {
                        // For payment requisitions, we might need to track line item status separately
                        // For now, update the header status
                        $record->status = $status;
                        $record->save();
                    }
                    break;

                case 'TNS':
                    $record = $this->tsallowance->find($sourceId);
                    if ($record) {
                        $record->status = $status;
                        $record->save();
                    }
                    break;

                case 'STAFF_WELFARE':
                    $record = $this->staffwelfareloan->find($sourceId);
                    if ($record) {
                        $record->status = $status;
                        $record->save();
                    }
                    break;
            }
        } catch (Exception $e) {
            // Log error but don't fail the operation
        }
    }
}
