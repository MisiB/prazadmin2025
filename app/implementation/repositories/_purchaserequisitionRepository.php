<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use App\Models\Departmentuser;
use App\Models\PaymentRequisition;
use App\Models\Purchaserequisition;
use App\Models\Purchaserequisitionapproval;
use App\Models\Purchaserequisitionaward;
use App\Models\Purchaserequisitionawarddelivery;
use App\Models\Purchaserequisitionawarddocument;
use App\Models\User;
use App\Models\Workflow;
use App\Notifications\AwaitingdeliveryNotification;
use App\Notifications\PurchaseRequisitionAlert;
use App\Notifications\PurchaseRequisitionNotification;
use App\Notifications\PurchaseRequisitionUpdate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class _purchaserequisitionRepository implements ipurchaseerequisitionInterface
{
    /**
     * Create a new class instance.
     */
    protected $purchaserequisition;

    protected $purchaserequisitionapproval;

    protected $purchaserequisitionaward;

    protected $purchaserequisitionawarddocument;

    protected $purchaserequisitionawarddelivery;

    protected $workflow;

    protected $budgetrepo;

    protected $departmentuser;

    protected $paymentrequisitionrepo;

    public function __construct(Purchaserequisition $purchaserequisition, Purchaserequisitionapproval $purchaserequisitionapproval, Purchaserequisitionaward $purchaserequisitionaward, Purchaserequisitionawarddocument $purchaserequisitionawarddocument, Purchaserequisitionawarddelivery $purchaserequisitionawarddelivery, Workflow $workflow, ibudgetInterface $budgetrepo, Departmentuser $departmentuser, ipaymentrequisitionInterface $paymentrequisitionrepo)
    {
        $this->purchaserequisition = $purchaserequisition;
        $this->purchaserequisitionapproval = $purchaserequisitionapproval;
        $this->purchaserequisitionaward = $purchaserequisitionaward;
        $this->purchaserequisitionawarddocument = $purchaserequisitionawarddocument;
        $this->purchaserequisitionawarddelivery = $purchaserequisitionawarddelivery;
        $this->workflow = $workflow;
        $this->budgetrepo = $budgetrepo;
        $this->departmentuser = $departmentuser;
        $this->paymentrequisitionrepo = $paymentrequisitionrepo;
    }

    public function getpurchaseerequisitions($year)
    {
        return $this->purchaserequisition->with('budgetitem.currency', 'department', 'requestedby', 'recommendedby', 'workflow.workflowparameters.permission')->whereNotIn('status', ['DRAFT', 'AWAITING_RECOMMENDATION', 'PENDING', 'NOT_RECOMMENDED'])->where('year', $year)->paginate(10);
    }

    public function getpurchaseerequisition($id)
    {
        return $this->purchaserequisition->with('budgetitem.currency', 'department', 'requestedby', 'recommendedby', 'awards.customer', 'awards.currency', 'awards.paymentcurrency', 'awards.secondpaymentcurrency', 'awards.documents', 'awards.createdby', 'awards.approvedby', 'awards.deliveredby', 'awards.deliveries.deliveredby')->find($id);
    }

    public function getpurchaseerequisitionbyuuid($uuid)
    {
        return $this->purchaserequisition->with('budgetitem.currency', 'department', 'requestedby', 'recommendedby', 'workflow.workflowparameters', 'approvals.user')->where('uuid', $uuid)->first();
    }

    public function getpurchaseerequisitionbydepartment($year, $department_id, $search = null)
    {
        $query = $this->purchaserequisition
            ->with('budgetitem.currency', 'department', 'requestedby', 'recommendedby')
            ->where('year', $year)
            ->where('department_id', $department_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('prnumber', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('budgetitem', function ($q) use ($search) {
                        $q->where('activity', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(10);
    }

    public function createpurchaseerequisition($data)
    {
        try {
            $budgetitem = $this->budgetrepo->getbudgetitem($data['budgetitem_id']);
            $workflowname = config('workflow.purchase_requisitions');
            $workflow = $this->workflow->where('name', $workflowname)->first();
            if ($workflow == null) {
                return ['status' => 'error', 'message' => 'Workflow Not Defined'];
            }
            $data['uuid'] = Str::uuid()->toString();
            $data['requested_by'] = Auth::user()->id;
            $data['workflow_id'] = $workflow->id;
            $data['year'] = date('Y');
            $data['prnumber'] = 'PR'.date('Y').random_int(1000, 9999999);
            $data['department_id'] = $budgetitem->department_id;
            $data['status'] = 'DRAFT';
            $this->purchaserequisition->create($data);

            return ['status' => 'success', 'message' => 'Purchase Requisition Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updatepurchaseerequisition($id, $data)
    {
        try {
            $record = $this->purchaserequisition->find($id);

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Purchase Requisition Cannot be Modified After Submission'];
            }

            $budgetitem = $this->budgetrepo->getbudgetitem($data['budgetitem_id']);
            $budgetitem_amount = $budgetitem->amount;
            $budgetitem_outgoingvirements = $budgetitem->outgoingvirements()->sum('amount');
            $budgetitem_incomingvirements = $budgetitem->incomingvirements()->sum('amount');
            $budgetitem_purchaserequisitions = $budgetitem->purchaserequisitions()->whereNotIn('status', ['DRAFT'])->sum('quantity') * $budgetitem->unitprice;
            $budgetitem_total = $budgetitem_amount - $budgetitem_outgoingvirements - $budgetitem_incomingvirements - $budgetitem_purchaserequisitions;
            $purchaserequisition_total = $budgetitem->unitprice * $data['quantity'];
            if ($budgetitem_total < $purchaserequisition_total) {
                return ['status' => 'error', 'message' => 'Budget Item Total is Less than Amount'];
            }
            $data['updated_by'] = Auth::user()->id;
            $record->update($data);

            return ['status' => 'success', 'message' => 'Purchase Requisition Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletepurchaseerequisition($id)
    {
        try {
            $record = $this->purchaserequisition->where('id', $id)->first();
            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Purchase Requisition Cannot be Deleted After Submission'];
            }
            $record->delete();

            return ['status' => 'success', 'message' => 'Purchase Requisition Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getpurchaseerequisitionbystatus($year, $status)
    {
        return $this->purchaserequisition->with('budgetitem.currency', 'department', 'requestedby', 'recommendedby', 'approvals.user', 'awards.customer', 'awards.currency')->where('year', $year)->where('status', $status)->paginate(10);
    }

    public function submitpurchaserequisition($id)
    {
        try {
            $record = $this->purchaserequisition->with('workflow.workflowparameters.permission', 'budgetitem')->where('id', $id)->first();

            if ($record->status != 'DRAFT') {
                return ['status' => 'error', 'message' => 'Purchase Requisition Already Submitted'];
            }

            // Validate required fields
            $requiredFields = ['budgetitem_id', 'purpose', 'quantity', 'description'];
            foreach ($requiredFields as $field) {
                if (empty($record->$field)) {
                    return ['status' => 'error', 'message' => "Field {$field} is required"];
                }
            }

            // Budget validation
            $budgetitem = $record->budgetitem;
            $budgetitem_amount = $budgetitem->total;
            $budgetitem_outgoingvirements = $budgetitem->outgoingvirements()->sum('amount');
            $budgetitem_incomingvirements = $budgetitem->incomingvirements()->sum('amount');
            $budgetitem_purchaserequisitions = $budgetitem->purchaserequisitions()->whereNotIn('status', ['DRAFT'])->sum('quantity') * $budgetitem->unitprice;
            $budgetitem_total = $budgetitem_amount - $budgetitem_outgoingvirements + $budgetitem_incomingvirements - $budgetitem_purchaserequisitions;

            $purchaserequisition_total = $budgetitem->unitprice * $record->quantity;
            if ($budgetitem_total < $purchaserequisition_total) {
                return ['status' => 'error', 'message' => 'Budget Item Total is Less than Amount'];
            }

            // Change status to AWAITING_RECOMMENDATION
            $record->status = 'AWAITING_RECOMMENDATION';
            $record->save();

            // Send notification to supervisor
            $departmentuser = $this->departmentuser->with('supervisor')->where('user_id', $record->requested_by)->first();
            if ($departmentuser && $departmentuser->supervisor) {
                $array = [];
                $array['budgetitem'] = $budgetitem->activity;
                $array['purpose'] = $record->purpose;
                $array['quantity'] = $record->quantity;
                $array['unitprice'] = $budgetitem->unitprice;
                $array['total'] = $purchaserequisition_total;
                $array['uuid'] = $record->uuid;
                Notification::send($departmentuser->supervisor, new PurchaseRequisitionAlert(collect($array)));
            }

            return ['status' => 'success', 'message' => 'Purchase Requisition Submitted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recommend($id, $data)
    {
        try {
            $record = $this->purchaserequisition->with('workflow.workflowparameters.permission', 'budgetitem.strategysubprogrammeoutput', 'department', 'requestedby')->where('id', $id)->first();
            if ($record->status != 'AWAITING_RECOMMENDATION') {
                return ['status' => 'error', 'message' => 'Purchase Requisition Cannot be Recommended'];
            }
            if ($data['decision'] == 'RECOMMEND') {
                $workflowparameter = $record->workflow->workflowparameters->where('order', 1)->first();
                $record->status = $workflowparameter->status;
                $record->recommended_by = Auth::user()->id;
                $record->save();
                $users = User::permission($workflowparameter->permission->name)->get();

                if ($users->count() > 0) {
                    $array = [];
                    $array['budgetitem'] = $record->budgetitem->activity;
                    $array['strategysubprogrammeoutput'] = $record->budgetitem->strategysubprogrammeoutput?->output ?? 'N/A';
                    $array['department'] = $record->department->name;
                    $array['requested_by'] = $record->requestedby->name ?? '';
                    $array['recommended_by'] = Auth::user()->name;
                    $array['purpose'] = $record->purpose;
                    $array['quantity'] = $record->quantity;
                    $array['unitprice'] = $record->unitprice;
                    $array['total'] = $record->total;
                    $array['status'] = $workflowparameter->status;
                    $array['uuid'] = $record->uuid;
                    Notification::send($users, new PurchaseRequisitionNotification($array));
                }

                // Add comment if provided
                if (! empty($data['comment'])) {
                    $comments = $record->comments ?? [];
                    $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                    $record->comments = $comments;
                    $record->save();
                }
            } else {
                $record->status = 'NOT_RECOMMENDED';
                $comments = $record->comments;
                $comments[] = ['user_id' => Auth::user()->name, 'comment' => $data['comment'], 'created_at' => now()];
                $record->comments = $comments;
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Purchase Requisition Recommended Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function makedecision($id, $data)
    {
        try {
            $record = $this->purchaserequisition->with('workflow.workflowparameters.permission', 'recommendedby', 'requestedby')->where('id', $id)->first();

            if (! $record) {
                return ['status' => 'error', 'message' => 'Purchase Requisition not found'];
            }

            if (! $record->workflow) {
                return ['status' => 'error', 'message' => 'Workflow not found for this requisition'];
            }

            $array = [];
            $users = new Collection;
            $array['budgetitem'] = $record->budgetitem->activity;
            $array['strategysubprogrammeoutput'] = $record->budgetitem->strategysubprogrammeoutput?->output ?? 'N/A';
            $array['department'] = $record->department->name;
            $array['requested_by'] = $record->requestedby->name ?? '';
            $array['recommended_by'] = $record->recommendedby->name ?? '';
            $array['purpose'] = $record->purpose;
            $array['quantity'] = $record->quantity;
            $array['unitprice'] = $record->unitprice;
            $array['total'] = $record->total;
            $array['uuid'] = $record->uuid;
            $array['status'] = $record->status;
            $workflowparameter = $record->workflow->workflowparameters->where('status', $record->status)->first();

            if (! $workflowparameter) {
                return ['status' => 'error', 'message' => 'Workflow parameter not found for current status'];
            }

            if ($data['decision'] == 'APPROVED') {
                $count = $record->workflow->workflowparameters->count();
                if ($workflowparameter->order + 1 <= $count) {
                    $payload = $record->workflow->workflowparameters->where('order', $workflowparameter->order + 1)->first();
                    if ($payload) {
                        $status = $payload->status;
                        $permission = $payload->permission->name;
                        if ($record->status == 'BUDGET_CONFIRMATION') {
                            $record->fundavailable = 'Y';
                        }
                        $record->status = $status;

                        $users = User::permission($permission)->get();
                    } else {
                        // No next step, move to AWAITING_PMU
                        $record->status = 'AWAITING_PMU';
                        $users = User::permission($workflowparameter->permission->name)->get();
                    }
                } else {
                    $record->status = 'AWAITING_PMU';
                    $users = User::permission($workflowparameter->permission->name)->get();
                }

                $record->save();

                if ($users->count() > 0) {
                    Notification::send($users, new PurchaseRequisitionNotification($array));
                }
            } else {
                $record->status = 'REJECTED';
                $record->save();
            }
            $this->purchaserequisitionapproval->create([
                'purchaserequisition_id' => $record->id,
                'workflowparameter_id' => $workflowparameter->id,
                'user_id' => Auth::user()->id,
                'status' => $data['decision'],
                'comment' => $data['comment'],
            ]);
            $array2 = [];
            $array2['step'] = $workflowparameter->status;
            $array2['status'] = $data['decision'];
            $array2['comment'] = $data['comment'];
            Notification::send($record->requestedby, new PurchaseRequisitionUpdate($array2));
            Notification::send($record->recommendedby, new PurchaseRequisitionUpdate($array2));
            if ($data['decision'] != 'APPROVED') {
                $record->status = 'REJECTED';
                $record->save();
            }

            return ['status' => 'success', 'message' => 'Purchase Requisition Updated Successfully'];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getpurchaserequeisitionbyworkflowparameter($year)
    {
        $data = $this->workflow->with(['workflowparameters.permission', 'workflowparameters.purchaserequisitionapprovals' => function ($query) use ($year) {
            $query->where('year', $year);
            $query->whereNotIn('status', ['DRAFT', 'AWAITING_RECOMMENDATION', 'PENDING', 'NOT_RECOMMENDED']);
        }])->where('name', 'purchase_requisitions')->first();

        return $data->workflowparameters;

    }

    public function createaward($data)
    {
        try {
            $data['created_by'] = Auth::user()->id;
            $data['uuid'] = Str::uuid()->toString();
            $this->purchaserequisitionaward->create($data);

            return ['status' => 'success', 'message' => 'Award Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateaward($id, $data)
    {
        try {
            $this->purchaserequisitionaward->find($id)->update($data);

            return ['status' => 'success', 'message' => 'Award Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteaward($id)
    {
        try {
            $this->purchaserequisitionaward->find($id)->delete();

            return ['status' => 'success', 'message' => 'Award Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getawards($year)
    {
        return $this->purchaserequisitionaward->with('purchaserequisition.budgetitem.currency', 'purchaserequisition.department', 'purchaserequisition.requestedby', 'purchaserequisition.recommendedby', 'purchaserequisition.workflow.workflowparameters.permission', 'customer', 'currency')->where('year', $year)->paginate(10);
    }

    public function getaward($id)
    {
        return $this->purchaserequisitionaward->with('purchaserequisition.budgetitem.currency', 'customer', 'purchaserequisition.department', 'purchaserequisition.requestedby', 'purchaserequisition.recommendedby', 'purchaserequisition.workflow.workflowparameters.permission', 'customer', 'currency', 'paymentcurrency', 'secondpaymentcurrency', 'documents', 'createdby', 'approvedby', 'deliveredby')->find($id);
    }

    public function approveaward($id)
    {
        try {
            $purchaserequisition = $this->purchaserequisition->where('id', $id)->first();
            foreach ($purchaserequisition->awards as $award) {
                $award->status = 'APPROVED';
                $award->approved_by = Auth::user()->id;
                $award->save();
            }
            $purchaserequisition->status = 'AWAITING_DELIVERY';
            $purchaserequisition->save();
            $users = User::permission('ADMIN.DELIVERY.ACCESS')->get();
            Notification::send($users, new AwaitingdeliveryNotification);

            return ['status' => 'success', 'message' => 'Award Approved Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function createawarddocument($data)
    {
        try {
            $this->purchaserequisitionawarddocument->create($data);

            return ['status' => 'success', 'message' => 'Award Document Created Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateawarddocument($id, $data)
    {
        try {
            $this->purchaserequisitionawarddocument->find($id)->update($data);

            return ['status' => 'success', 'message' => 'Award Document Updated Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteawarddocument($id)
    {
        try {
            $this->purchaserequisitionawarddocument->find($id)->delete();

            return ['status' => 'success', 'message' => 'Award Document Deleted Successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getawarddocuments($id)
    {
        return $this->purchaserequisitionawarddocument->where('purchaserequisitionaward_id', $id)->get();
    }

    public function recorddelivery($awardId, $data)
    {
        try {
            $award = $this->purchaserequisitionaward->with('purchaserequisition.budgetitem', 'purchaserequisition.department', 'purchaserequisition.requestedby', 'customer')->find($awardId);
            if (! $award) {
                return ['status' => 'error', 'message' => 'Award not found'];
            }

            $quantityDelivered = (int) $data['quantity_delivered'];
            $currentDelivered = $award->quantity_delivered ?? 0;
            $currentPaid = $award->quantity_paid ?? 0;
            $newTotalDelivered = $currentDelivered + $quantityDelivered;

            if ($newTotalDelivered > $award->quantity) {
                return ['status' => 'error', 'message' => 'Quantity delivered cannot exceed award quantity'];
            }

            // Create delivery record
            $deliveryRecord = $this->purchaserequisitionawarddelivery->create([
                'purchaserequisitionaward_id' => $awardId,
                'quantity_delivered' => $quantityDelivered,
                'delivery_date' => $data['delivery_date'] ?? now(),
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'invoice_filepath' => $data['invoice_filepath'] ?? null,
                'delivery_note_filepath' => $data['delivery_note_filepath'] ?? null,
                'tax_clearance_filepath' => $data['tax_clearance_filepath'] ?? null,
                'delivered_by' => Auth::user()->id,
            ]);

            // Update award totals
            $award->quantity_delivered = $newTotalDelivered;
            $award->delivery_date = $data['delivery_date'] ?? now();
            $award->delivery_notes = $data['delivery_notes'] ?? null;
            $award->delivered_by = Auth::user()->id;
            $award->save();

            // Calculate payment eligible quantity (delivered - paid)
            $paymentEligibleQuantity = $newTotalDelivered - $currentPaid;

            // If there's payment eligible quantity, create payment requisition immediately
            if ($paymentEligibleQuantity > 0) {
                $this->createPaymentRequisitionForDeliveredQuantities($award, $paymentEligibleQuantity, $deliveryRecord);
            }

            // Check if all awards for this requisition are fully delivered
            $requisition = $award->purchaserequisition;
            $allAwards = $requisition->awards;
            $allFullyDelivered = $allAwards->every(function ($award) {
                return ($award->quantity_delivered ?? 0) >= $award->quantity;
            });

            // Update requisition status based on delivery status
            if ($allFullyDelivered && $requisition->status == 'AWAITING_DELIVERY') {
                // All items fully delivered - mark requisition as Completed
                $requisition->status = 'Completed';
                $requisition->save();
            }

            return ['status' => 'success', 'message' => 'Delivery recorded successfully'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Create payment requisition for delivered quantities (Alternate Flow - PR-driven)
     * This is called immediately when delivery is recorded
     */
    private function createPaymentRequisitionForDeliveredQuantities($award, int $paymentEligibleQuantity, $deliveryRecord)
    {
        try {
            $requisition = $award->purchaserequisition;
            $pr = $requisition->load('budgetitem', 'department', 'requestedby');

            // Calculate unit price (amount / quantity or use unitprice if available)
            $unitPrice = $award->unitprice ?? ($award->quantity > 0 ? $award->amount / $award->quantity : 0);
            $lineTotal = $paymentEligibleQuantity * $unitPrice;

            if ($lineTotal <= 0) {
                return; // No amount to pay
            }

            // Get currency from award or budget item
            $currencyId = $award->currency_id ?? $pr->budgetitem->currency_id ?? 1;

            // Prepare delivery documents for attachment
            $attachments = [];
            if ($deliveryRecord->invoice_filepath) {
                $attachments['invoice'] = $deliveryRecord->invoice_filepath;
            }
            if ($deliveryRecord->delivery_note_filepath) {
                $attachments['delivery_note'] = $deliveryRecord->delivery_note_filepath;
            }
            if ($deliveryRecord->tax_clearance_filepath) {
                $attachments['tax_clearance'] = $deliveryRecord->tax_clearance_filepath;
            }

            // Format delivery date
            $deliveryDateFormatted = $deliveryRecord->delivery_date instanceof Carbon
                ? $deliveryRecord->delivery_date->format('Y-m-d')
                : Carbon::parse($deliveryRecord->delivery_date)->format('Y-m-d');

            // Create payment requisition data
            $paymentRequisitionData = [
                'source_type' => 'PURCHASE_REQUISITION',
                'source_id' => $pr->id,
                'budget_id' => $pr->budgetitem->budget_id,
                'budget_line_item_id' => $pr->budgetitem_id,
                'purpose' => 'Payment for Purchase Requisition: '.$pr->prnumber.' - '.$pr->purpose.' (Delivery: '.$deliveryDateFormatted.')',
                'department_id' => $pr->department_id,
                'currency_id' => $currencyId,
                'status' => 'HOD_RECOMMENDED', // Skip DRAFT, SUBMITTED - enter at HOD_RECOMMENDED
                'created_by' => $pr->requested_by,
                'line_items' => [
                    [
                        'quantity' => $paymentEligibleQuantity,
                        'description' => 'Payment for delivery to '.($award->customer->name ?? 'Supplier').' - Tender: '.$award->tendernumber.($deliveryRecord->delivery_notes ? ' - '.$deliveryRecord->delivery_notes : ''),
                        'unit_amount' => $unitPrice,
                        'line_total' => $lineTotal,
                    ],
                ],
                'attachments' => $attachments,
            ];

            // Create the payment requisition
            $result = $this->paymentrequisitionrepo->createpaymentrequisition($paymentRequisitionData);

            // Update paid quantity if payment requisition was created successfully
            if ($result['status'] == 'success') {
                $award->quantity_paid = ($award->quantity_paid ?? 0) + $paymentEligibleQuantity;
                $award->save();
            }

        } catch (Exception $e) {
            // Log error but don't fail the delivery process
            \Log::error('Failed to auto-create payment requisition for delivered quantities: '.$e->getMessage());
        }
    }

    /**
     * Auto-create payment requisition from purchase requisition when it reaches AWAITING_PAYMENT status
     *
     * @deprecated This method is kept for backward compatibility but is replaced by createPaymentRequisitionForDeliveredQuantities
     */
    private function createPaymentRequisitionFromPurchaseRequisition(Purchaserequisition $purchaserequisition)
    {
        try {
            // Check if payment requisition already exists for this purchase requisition
            $existingPaymentRequisition = PaymentRequisition::where('source_type', 'PURCHASE_REQUISITION')
                ->where('source_id', $purchaserequisition->id)
                ->first();

            if ($existingPaymentRequisition) {
                return; // Already created
            }

            // Get purchase requisition with relationships
            $pr = $purchaserequisition->load('budgetitem', 'department', 'requestedby', 'awards.customer');

            // Calculate total amount from unpaid delivered quantities only
            $totalAmount = 0;
            $lineItems = [];

            foreach ($pr->awards as $award) {
                $quantityDelivered = $award->quantity_delivered ?? 0;
                $quantityPaid = $award->quantity_paid ?? 0;
                $paymentEligibleQuantity = $quantityDelivered - $quantityPaid;

                if ($paymentEligibleQuantity > 0) {
                    $unitPrice = $award->unitprice ?? ($award->quantity > 0 ? $award->amount / $award->quantity : 0);
                    $lineTotal = $paymentEligibleQuantity * $unitPrice;
                    $totalAmount += $lineTotal;

                    $lineItems[] = [
                        'quantity' => $paymentEligibleQuantity,
                        'description' => 'Payment for award to '.($award->customer->name ?? 'Supplier').' - Tender: '.$award->tendernumber,
                        'unit_amount' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }
            }

            if ($totalAmount <= 0) {
                return; // No amount to pay
            }

            // Get currency from budget item or first award
            $currencyId = $pr->budgetitem->currency_id ?? $pr->awards->first()?->currency_id ?? 1;

            // Create payment requisition data
            $paymentRequisitionData = [
                'source_type' => 'PURCHASE_REQUISITION',
                'source_id' => $pr->id,
                'budget_id' => $pr->budgetitem->budget_id,
                'budget_line_item_id' => $pr->budgetitem_id,
                'purpose' => 'Payment for Purchase Requisition: '.$pr->prnumber.' - '.$pr->purpose,
                'department_id' => $pr->department_id,
                'currency_id' => $currencyId,
                'status' => 'HOD_RECOMMENDED', // Skip DRAFT, SUBMITTED - enter at HOD_RECOMMENDED
                'created_by' => $pr->requested_by,
                'line_items' => $lineItems,
            ];

            // Create the payment requisition
            $result = $this->paymentrequisitionrepo->createpaymentrequisition($paymentRequisitionData);

            // Update paid quantities if payment requisition was created successfully
            if ($result['status'] == 'success') {
                foreach ($pr->awards as $award) {
                    $quantityDelivered = $award->quantity_delivered ?? 0;
                    $quantityPaid = $award->quantity_paid ?? 0;
                    $paymentEligibleQuantity = $quantityDelivered - $quantityPaid;

                    if ($paymentEligibleQuantity > 0) {
                        $award->quantity_paid = $quantityPaid + $paymentEligibleQuantity;
                        $award->save();
                    }
                }
            }

        } catch (Exception $e) {
            // Log error but don't fail the delivery process
            \Log::error('Failed to auto-create payment requisition from purchase requisition: '.$e->getMessage());
        }
    }
}
