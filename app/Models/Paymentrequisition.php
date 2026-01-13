<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentRequisition extends Model
{
    protected $table = 'payment_requisitions';

    protected function casts(): array
    {
        return [
            'comments' => 'collection',
            'total_amount' => 'decimal:2',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function budgetLineItem(): BelongsTo
    {
        return $this->belongsTo(Budgetitem::class, 'budget_line_item_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function recommendedByHod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by_hod', 'id');
    }

    public function reviewedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin', 'id');
    }

    public function recommendedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by_admin', 'id');
    }

    public function approvedByFinal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_final', 'id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(PaymentRequisitionLineItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PaymentRequisitionApproval::class);
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(Purchaserequisition::class, 'source_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Paymentrequisitiondocument::class, 'paymentrequisition_id');
    }
}
