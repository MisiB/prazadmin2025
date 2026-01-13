<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequisitionApproval extends Model
{
    protected $table = 'payment_requisition_approvals';

    public function paymentRequisition(): BelongsTo
    {
        return $this->belongsTo(PaymentRequisition::class);
    }

    public function workflowParameter(): BelongsTo
    {
        return $this->belongsTo(Workflowparameter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
