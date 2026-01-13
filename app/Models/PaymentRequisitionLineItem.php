<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paymentrequisitionlineitem extends Model
{
    protected $table = 'payment_requisition_line_items';

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function paymentRequisition(): BelongsTo
    {
        return $this->belongsTo(PaymentRequisition::class);
    }
}
