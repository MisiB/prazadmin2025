<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentVoucher extends Model
{
    protected $table = 'payment_vouchers';
  //
  
    protected function casts(): array
    {
        return [
            'voucher_date' => 'date',
            'exchange_rate' => 'decimal:4',
            'total_amount' => 'decimal:2',
        ];
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by', 'id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by', 'id');
    }

    public function financeApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_approved_by', 'id');
    }

    public function ceoApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ceo_approved_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentVoucherItem::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(PaymentVoucherAuditLog::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Bankaccount::class, 'bank_account_id');
    }
}
