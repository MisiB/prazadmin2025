<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffWelfareLoanConfig extends Model
{
    protected $fillable = [
        'interest_rate',
        'max_repayment_months',
        'max_loan_amount',
        'min_loan_amount',
        'is_active',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'interest_rate' => 'decimal:2',
            'max_loan_amount' => 'decimal:2',
            'min_loan_amount' => 'decimal:2',
            'max_repayment_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
