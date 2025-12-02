<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReversedTransaction extends Model
{
    protected $fillable = [
        'suspense_utilization_id',
        'suspense_id',
        'invoice_id',
        'invoice_number',
        'receipt_number',
        'amount',
        'rate',
        'reversed_by',
        'reversed_at',
        'original_data',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'original_data' => 'array',
            'reversed_at' => 'datetime',
        ];
    }

    public function suspense(): BelongsTo
    {
        return $this->belongsTo(Suspense::class, 'suspense_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
