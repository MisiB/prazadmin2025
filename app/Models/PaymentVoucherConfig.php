<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucherConfig extends Model
{
    protected $table = 'payment_voucher_configs';

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }
}
