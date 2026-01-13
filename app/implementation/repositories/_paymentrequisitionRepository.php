<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Models\Departmentuser;
use App\Models\PaymentRequisition;
use App\Models\PaymentRequisitionApproval;
use App\Models\PaymentRequisitionDocument;
use App\Models\PaymentRequisitionLineItem;
use App\Models\User;
use App\Models\Workflow;
use App\Notifications\PaymentRequisitionAlert;
use App\Notifications\PaymentRequisitionNotification;
use App\Notifications\PaymentRequisitionUpdate;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class _paymentrequisitionRepository implements ipaymentrequisitionInterface
{
    protected $paymentrequisition;

    protected $paymentrequisitionapproval;

    protected $paymentrequisitionlineitem;

    protected $workflow;

    protected $budgetrepo;

    protected $departmentuser;

    protected $paymentrequisitiondocument;

    public function __construct(PaymentRequisition $paymentrequisition, PaymentRequisitionApproval $paymentrequisitionapproval, PaymentRequisitionLineItem $paymentrequisitionlineitem, PaymentRequisitionDocument $paymentrequisitiondocument, Workflow $workflow, ibudgetInterface $budgetrepo, Departmentuser $departmentuser)
    {
        $this->paymentrequisition = $paymentrequisition;
        $this->paymentrequisitionapproval = $paymentrequisitionapproval;
        $this->paymentrequisitionlineitem = $paymentrequisitionlineitem;
        $this->paymentrequisitiondocument = $paymentrequisitiondocument;
        $this->workflow = $workflow;
        $this->budgetrepo = $budgetrepo;
        $this->departmentuser = $departmentuser;
    }

    public function getpaymentrequisitions($year, $search = null)
    {
        $query = $this->paymentrequisition->with('budgetLineItem.currency', 'budget', 'department', 'createdBy', 'currency', 'workflow.workflowparameters.permission')
            ->where('year', $year)
            ->whereNotIn('status', ['DRAFT']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getpaymentrequisitionsbyapplicant($userId, $year, $search = null)
    {
        $query = $this->paymentrequisition->with('budgetLineItem.currency', 'budget', 'department', 'createdBy', 'currency', 'workflow.workflowparameters.permission')
            ->where('year', $year)
            ->where('created_by', $userId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getpaymentrequisition($id)
    {
        return $this->paymentrequisition->with('budgetLineItem.currency', 'budget', 'department', 'createdBy', 'currency', 'lineItems', 'workflow.workflowparameters.permission', 'approvals.user')->find($id);
    }

    public function getpaymentrequisitionbyuuid($uuid)
    {
        return $this->paymentrequisition->with('budgetLineItem.currency', 'budget', 'department', 'createdBy', 'currency', 'lineItems', 'documents', 'workflow.workflowparameters.permission', 'approvals.user')->where('uuid', $uuid)->first();
    }

    public function getpaymentrequisitionbydepartment($year, $department_id, $search = null)
    {
        $query = $this->paymentrequisition
            ->with('budgetLineItem.currency', 'budget', 'department', 'createdBy', 'currency')
            ->where('year', $year)
            ->where('department_id', $department_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function createpaymentrequisition($data)
    {
        try {
            $budgetitem = $this->budgetrepo->getbudgetitem($data['budget_line_item_id']);
            $workflowname = config('workflow.payment_requisitions');
            $workflow = $this->workflow->where('name', $workflowname)->first();
            if ($workflow == null) {
                return ['status' => 'error', 'message' => 'Workflow Not Defined'];
            }

            // Extract line_items and attachments before creating the payment requisition
            $lineItems = $data['line_items'] ?? [];
            $attachments = $data['attachments'] ?? [];
            unset($data['line_items'], $data['attachments']); // Remove from data array

            // Set source_type and source_id automatically
            $data['source_type'] = $data['source_type'] ?? 'USER';
            $data['source_id'] = $data['source_id'] ?? null;

            // Prepare payment requisition data
            $paymentRequisitionData = [
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'],
                'budget_id' => $data['budget_id'],
                'budget_line_item_id' => $data['budget_line_item_id'],
                'purpose' => $data['purpose'],
                'department_id' => $budgetitem->department_id,
                'currency_id' => $data['currency_id'],
                'total_amount' => 0, // Will be calculated from line items
                'status' => $data['status'] ?? 'DRAFT',
                'uuid' => Str::uuid()->toString(),
                'created_by' => $data['created_by'] ?? Auth::user()->id,
                'workflow_id' => $workflow->id,
                'year' => date('Y'),
                'reference_number' => 'PAYREQ'.date('Y').random_int(1000, 9999999),
            ];

            $paymentrequisition = $this->paymentrequisition->create($paymentRequisitionData);

            // Create line items if provided
            if (! empty($lineItems) && is_array($lineItems)) {
                $totalAmount = 0;
                foreach ($lineItems as $lineItemData) {
                    $lineTotal = $lineItemData['quantity'] * $lineItemData['unit_amount'];
                    $totalAmount += $lineTotal;
                    $this->paymentrequisitionlineitem->create([
                        'payment_requisition_id' => $paymentrequisition->id,
                        'quantity' => $lineItemData['quantity'],
                        'description' => $lineItemData['description'],
                        'unit_amount' => $lineItemData['unit_amount'],
                        'line_total' => $lineTotal,
                    ]);
                }
                $paymentrequisition->total_amount = $totalAmount;
                $paymentrequisition->save();
            }

            // Create documents if provided
            if (! empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $type => $filepath) {
                    $documentType = 'other';
                    $documentName = 'Other Document';

                    if ($type === 'invoice') {
                        $documentType = 'invoice';
                        $documentName = 'Invoice';
                    } elseif ($type === 'tax_clearance') {
                        $documentType = 'tax_clearance';
                        $documentName = 'Tax Clearance';
                    } elseif ($type === 'delivery_note') {
                        $documentType = 'other';
                        $documentName = 'Delivery Note';
                    } elseif (str_starts_with($type, 'other_')) {
                        $documentType = 'other';
                        $documentName = 'Other Document';
                    }

                    $this->paymentrequisitiondocument->create([
                        'paymentrequisition_id' => $paymentrequisition->id,
                        'document_type' => $documentType,
                        'document' => $documentName,
                        'filepath' => $filepath,
                    ]);
                }
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updatepaymentrequisition($id, $data)
    {
        try {
            $record = $this->paymentrequisition->find($id);

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Payment Requisition Cannot be Modified After Submission'];
            }

            // Extract line_items before updating
            $lineItems = $data['line_items'] ?? null;
            unset($data['line_items']); // Remove line_items from data array

            // Don't allow updating source_type or source_id
            unset($data['source_type'], $data['source_id']);

            $budgetitem = $this->budgetrepo->getbudgetitem($data['budget_line_item_id']);
            $data['department_id'] = $budgetitem->department_id;

            $record->update($data);

            // Update line items if provided
            if ($lineItems !== null && is_array($lineItems)) {
                // Delete existing line items
                $record->lineItems()->delete();

                // Create new line items
                $totalAmount = 0;
                foreach ($lineItems as $lineItemData) {
                    $lineTotal = $lineItemData['quantity'] * $lineItemData['unit_amount'];
                    $totalAmount += $lineTotal;
                    $this->paymentrequisitionlineitem->create([
                        'payment_requisition_id' => $record->id,
                        'quantity' => $lineItemData['quantity'],
                        'description' => $lineItemData['description'],
                        'unit_amount' => $lineItemData['unit_amount'],
                        'line_total' => $lineTotal,
                    ]);
                }
                $record->total_amount = $totalAmount;
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletepaymentrequisition($id)
    {
        try {
            $record = $this->paymentrequisition->where('id', $id)->first();
            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Payment Requisition Cannot be Deleted After Submission'];
            }
            $record->delete();

            return ['status' => 'success', 'message' => 'Payment Requisition Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getpaymentrequisitionbystatus($year, $status)
    {
        return $this->paymentrequisition->with('budgetLineItem.currency', 'budget', 'department', 'createdBy', 'currency', 'approvals.user')
            ->where('year', $year)
            ->where('status', $status)
            ->paginate(10);
    }

    public function submitpaymentrequisition($id)
    {
        try {
            $record = $this->paymentrequisition->with('workflow.workflowparameters.permission', 'budgetLineItem')->where('id', $id)->first();

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Payment Requisition Already Submitted'];
            }

            // Validate required fields
            $requiredFields = ['budget_line_item_id', 'purpose', 'department_id', 'currency_id'];
            foreach ($requiredFields as $field) {
                if (empty($record->$field)) {
                    return ['status' => 'error', 'message' => "Field {$field} is required"];
                }
            }

            // Check if line items exist
            if ($record->lineItems()->count() == 0) {
                return ['status' => 'error', 'message' => 'At least one line item is required'];
            }

            // Budget validation - skip if currency is ZiG
            $currency = $record->currency;
            $isZigCurrency = $currency && strtoupper($currency->name) === 'ZIG';

            if (! $isZigCurrency) {
                $budgetitem = $record->budgetLineItem;
                $budgetitem_amount = $budgetitem->total;
                $budgetitem_outgoingvirements = $budgetitem->outgoingvirements()->sum('amount');
                $budgetitem_incomingvirements = $budgetitem->incomingvirements()->sum('amount');
                $budgetitem_purchaserequisitions = $budgetitem->purchaserequisitions()->whereNotIn('status', ['DRAFT'])->sum('quantity') * $budgetitem->unitprice;
                $budgetitem_total = $budgetitem_amount - $budgetitem_outgoingvirements + $budgetitem_incomingvirements - $budgetitem_purchaserequisitions;

                if ($budgetitem_total < $record->total_amount) {
                    return ['status' => 'error', 'message' => 'Budget Item Total is Less than Payment Requisition Amount'];
                }
            }

            // Change status to Submitted
            $record->status = 'Submitted';
            $record->save();

            // Send notification to HOD
            $departmentuser = $this->departmentuser->with('supervisor')->where('user_id', $record->created_by)->first();
            if ($departmentuser && $departmentuser->supervisor) {
                $array = [];
                $array['budgetitem'] = $budgetitem->activity;
                $array['purpose'] = $record->purpose;
                $array['total'] = number_format($record->total_amount, 2);
                $array['uuid'] = $record->uuid;
                Notification::send($departmentuser->supervisor, new PaymentRequisitionAlert(collect($array)));
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Submitted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recommendhod($id, $data)
    {
        try {
            $record = $this->paymentrequisition->with('workflow.workflowparameters.permission', 'budgetLineItem', 'department', 'createdBy')->where('id', $id)->first();
            if ($record->status != 'Submitted') {
                return ['status' => 'error', 'message' => 'Payment Requisition Cannot be Recommended at this stage'];
            }
            if ($data['decision'] == 'RECOMMEND') {
                $record->status = 'HOD_RECOMMENDED';
                $record->recommended_by_hod = Auth::user()->id;
                $record->save();

                // Get users with admin review permission
                $workflowparameter = $record->workflow->workflowparameters->where('status', 'ADMIN_REVIEWED')->first();
                if ($workflowparameter) {
                    $users = User::permission($workflowparameter->permission->name)->get();

                    if ($users->count() > 0) {
                        $array = [];
                        $array['budgetitem'] = $record->budgetLineItem->activity;
                        $array['department'] = $record->department->name;
                        $array['created_by'] = $record->createdBy->name ?? '';
                        $array['recommended_by'] = Auth::user()->name;
                        $array['purpose'] = $record->purpose;
                        $array['total'] = number_format($record->total_amount, 2);
                        $array['status'] = 'HOD_RECOMMENDED';
                        $array['uuid'] = $record->uuid;
                        Notification::send($users, new PaymentRequisitionNotification($array));
                    }
                }

                // Add comment if provided
                if (! empty($data['comment'])) {
                    $comments = $record->comments ?? [];
                    $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                    $record->comments = $comments;
                    $record->save();
                }

                // Create approval record - HOD recommendation doesn't have a workflow parameter (handled separately)
                // Find workflow parameter for HOD_RECOMMENDED status (order 1 - Admin Review stage)
                $workflowparameter = $record->workflow->workflowparameters->where('order', 1)->first();
                if ($workflowparameter) {
                    $this->paymentrequisitionapproval->create([
                        'payment_requisition_id' => $record->id,
                        'workflowparameter_id' => $workflowparameter->id,
                        'user_id' => Auth::user()->id,
                        'status' => 'RECOMMEND',
                        'comment' => $data['comment'] ?? null,
                    ]);
                }
            } else {
                $record->status = 'Rejected';
                $comments = $record->comments ?? [];
                $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                $record->comments = $comments;
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Recommendation Processed Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function reviewadmin($id, $data)
    {
        try {
            $record = $this->paymentrequisition->with('workflow.workflowparameters.permission', 'budgetLineItem', 'department', 'createdBy', 'recommendedByHod')->where('id', $id)->first();

            if ($record->status != 'HOD_RECOMMENDED') {
                return ['status' => 'error', 'message' => 'Payment Requisition Cannot be Reviewed at this stage'];
            }

            if ($data['decision'] == 'APPROVED') {
                $record->status = 'ADMIN_REVIEWED';
                $record->reviewed_by_admin = Auth::user()->id;
                $record->save();

                // Get users with admin recommend permission
                $workflowparameter = $record->workflow->workflowparameters->where('status', 'ADMIN_RECOMMENDED')->first();
                if ($workflowparameter) {
                    $users = User::permission($workflowparameter->permission->name)->get();

                    if ($users->count() > 0) {
                        $array = [];
                        $array['budgetitem'] = $record->budgetLineItem->activity;
                        $array['department'] = $record->department->name;
                        $array['created_by'] = $record->createdBy->name ?? '';
                        $array['reviewed_by'] = Auth::user()->name;
                        $array['purpose'] = $record->purpose;
                        $array['total'] = number_format($record->total_amount, 2);
                        $array['status'] = 'ADMIN_REVIEWED';
                        $array['uuid'] = $record->uuid;
                        Notification::send($users, new PaymentRequisitionNotification($array));
                    }
                }

                // Add comment if provided
                if (! empty($data['comment'])) {
                    $comments = $record->comments ?? [];
                    $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                    $record->comments = $comments;
                    $record->save();
                }

                // Create approval record - find workflow parameter for Admin Review (order 1)
                // This approval happens when requisition is at HOD_RECOMMENDED status
                $workflowparameter = $record->workflow->workflowparameters->where('order', 1)->first();
                if ($workflowparameter) {
                    $this->paymentrequisitionapproval->create([
                        'payment_requisition_id' => $record->id,
                        'workflowparameter_id' => $workflowparameter->id,
                        'user_id' => Auth::user()->id,
                        'status' => 'APPROVED',
                        'comment' => $data['comment'] ?? null,
                    ]);
                }
            } else {
                $record->status = 'Rejected';
                $comments = $record->comments ?? [];
                $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                $record->comments = $comments;
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Review Processed Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recommendadmin($id, $data)
    {
        try {
            $record = $this->paymentrequisition->with('workflow.workflowparameters.permission', 'budgetLineItem', 'department', 'createdBy')->where('id', $id)->first();

            if ($record->status != 'ADMIN_REVIEWED') {
                return ['status' => 'error', 'message' => 'Payment Requisition Cannot be Recommended at this stage'];
            }

            if ($data['decision'] == 'RECOMMEND') {
                $record->status = 'ADMIN_RECOMMENDED';
                $record->recommended_by_admin = Auth::user()->id;
                $record->save();

                // Get users with final approval permission
                $workflowparameter = $record->workflow->workflowparameters->where('status', 'AWAITING_PAYMENT_VOUCHER')->first();
                if ($workflowparameter) {
                    $users = User::permission($workflowparameter->permission->name)->get();

                    if ($users->count() > 0) {
                        $array = [];
                        $array['budgetitem'] = $record->budgetLineItem->activity;
                        $array['department'] = $record->department->name;
                        $array['created_by'] = $record->createdBy->name ?? '';
                        $array['recommended_by'] = Auth::user()->name;
                        $array['purpose'] = $record->purpose;
                        $array['total'] = number_format($record->total_amount, 2);
                        $array['status'] = 'ADMIN_RECOMMENDED';
                        $array['uuid'] = $record->uuid;
                        Notification::send($users, new PaymentRequisitionNotification($array));
                    }
                }

                // Add comment if provided
                if (! empty($data['comment'])) {
                    $comments = $record->comments ?? [];
                    $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                    $record->comments = $comments;
                    $record->save();
                }

                // Create approval record - find workflow parameter for Admin Recommend (order 2)
                // This approval happens when requisition is at ADMIN_REVIEWED status
                $workflowparameter = $record->workflow->workflowparameters->where('order', 2)->first();
                if ($workflowparameter) {
                    $this->paymentrequisitionapproval->create([
                        'payment_requisition_id' => $record->id,
                        'workflowparameter_id' => $workflowparameter->id,
                        'user_id' => Auth::user()->id,
                        'status' => 'RECOMMEND',
                        'comment' => $data['comment'] ?? null,
                    ]);
                }
            } else {
                $record->status = 'Rejected';
                $comments = $record->comments ?? [];
                $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                $record->comments = $comments;
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Recommendation Processed Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approvefinal($id, $data)
    {
        try {
            $record = $this->paymentrequisition->with('workflow.workflowparameters.permission', 'budgetLineItem', 'department', 'createdBy')->where('id', $id)->first();

            if ($record->status != 'ADMIN_RECOMMENDED') {
                return ['status' => 'error', 'message' => 'Payment Requisition Cannot be Approved at this stage'];
            }

            if ($data['decision'] == 'APPROVED') {
                $record->status = 'AWAITING_PAYMENT';
                $record->approved_by_final = Auth::user()->id;
                $record->save();

                // Add comment if provided
                if (! empty($data['comment'])) {
                    $comments = $record->comments ?? [];
                    $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                    $record->comments = $comments;
                    $record->save();
                }

                // Create approval record - find workflow parameter for Final Approval (order 3)
                // This approval happens when requisition is at ADMIN_RECOMMENDED status
                $workflowparameter = $record->workflow->workflowparameters->where('order', 3)->first();
                if ($workflowparameter) {
                    $this->paymentrequisitionapproval->create([
                        'payment_requisition_id' => $record->id,
                        'workflowparameter_id' => $workflowparameter->id,
                        'user_id' => Auth::user()->id,
                        'status' => 'APPROVED',
                        'comment' => $data['comment'] ?? null,
                    ]);
                }

                // Notify requester
                $array = [];
                $array['status'] = 'AWAITING_PAYMENT';
                $array['comment'] = $data['comment'] ?? '';
                Notification::send($record->createdBy, new PaymentRequisitionUpdate($array));
            } else {
                $record->status = 'Rejected';
                $comments = $record->comments ?? [];
                $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                $record->comments = $comments;
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Payment Requisition Approval Processed Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function reject($id, $data)
    {
        try {
            $record = $this->paymentrequisition->find($id);

            $record->status = 'Rejected';
            $comments = $record->comments ?? [];
            $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
            $record->comments = $comments;
            $record->save();

            // Notify requester
            $array = [];
            $array['status'] = 'Rejected';
            $array['comment'] = $data['comment'] ?? '';
            Notification::send($record->createdBy, new PaymentRequisitionUpdate($array));

            return ['status' => 'success', 'message' => 'Payment Requisition Rejected Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getpaymentrequisitionsbyworkflowparameter($year)
    {
        $data = $this->workflow->with(['workflowparameters.permission', 'workflowparameters.paymentrequisitionapprovals' => function ($query) use ($year) {
            $query->where('year', $year);
            $query->whereNotIn('status', ['DRAFT', 'Submitted', 'Rejected']);
        }])->where('name', config('workflow.payment_requisitions'))->first();

        return $data->workflowparameters ?? collect();
    }
}
