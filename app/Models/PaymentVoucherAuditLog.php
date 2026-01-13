<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paymentvoucherauditlog extends Model
{
    protected $table = 'payment_voucher_audit_logs';

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:4',
            'timestamp' => 'datetime',
        ];
    }

    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
