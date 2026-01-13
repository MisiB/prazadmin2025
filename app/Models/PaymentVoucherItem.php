<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paymentvoucheritem extends Model
{
    protected $table = 'payment_voucher_items';

    protected $fillable = [
        'payment_voucher_id',
        'source_type',
        'source_id',
        'source_line_id',
        'description',
        'original_currency',
        'original_amount',
        'edited_amount',
        'amount_change_comment',
        'account_type',
        'gl_code',
        'exchange_rate',
        'payable_amount',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'edited_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'payable_amount' => 'decimal:2',
        ];
    }

    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    /**
     * Get the source record based on source_type and source_id
     */
    public function getSourceRecordAttribute()
    {
        return match ($this->source_type) {
            'PAYMENT_REQUISITION' => PaymentRequisition::find($this->source_id),
            'TNS' => TsAllowance::find($this->source_id),
            'STAFF_WELFARE' => StaffWelfareLoan::find($this->source_id),
            default => null,
        };
    }
}
