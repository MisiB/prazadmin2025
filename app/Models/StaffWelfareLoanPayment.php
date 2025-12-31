<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffWelfareLoanPayment extends Model
{
    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'amount_paid_original' => 'decimal:2',
            'amount_paid_usd' => 'decimal:2',
            'exchange_rate_used' => 'decimal:4',
            'payment_date' => 'date',
        ];
    }

    public function staffWelfareLoan(): BelongsTo
    {
        return $this->belongsTo(StaffWelfareLoan::class);
    }

    public function financeOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_officer_user_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function exchangerate(): BelongsTo
    {
        return $this->belongsTo(Exchangerate::class);
    }
}
