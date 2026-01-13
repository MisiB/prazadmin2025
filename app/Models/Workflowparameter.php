<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;

class Workflowparameter extends Model
{
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function purchaserequisitionapprovals()
    {
        return $this->hasMany(Purchaserequisitionapproval::class);
    }

    public function staffwelfareloanapprovals()
    {
        return $this->hasMany(StaffWelfareLoanApproval::class);
    }

    public function tsallowanceapprovals()
    {
        return $this->hasMany(TsAllowanceApproval::class);
    }

    public function paymentrequisitionapprovals()
    {
        return $this->hasMany(PaymentRequisitionApproval::class);
    }

    public function paymentvoucherapprovals()
    {
        return $this->hasMany(PaymentVoucherApproval::class);
    }
}
