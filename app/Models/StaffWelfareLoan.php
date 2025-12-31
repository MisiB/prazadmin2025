<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffWelfareLoan extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'comments' => 'collection',
            'loan_amount_requested' => 'decimal:2',
            'basic_salary' => 'decimal:2',
            'monthly_deduction_amount' => 'decimal:2',
            'existing_loan_balance' => 'decimal:2',
            'monthly_repayment' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'interest_rate_applied' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'total_repayment_amount' => 'decimal:2',
            'applicant_digital_declaration' => 'boolean',
            'hr_digital_confirmation' => 'boolean',
            'finance_officer_confirmation' => 'boolean',
            'employee_digital_acceptance' => 'boolean',
            'submission_date' => 'datetime',
            'hr_review_date' => 'datetime',
            'payment_date' => 'date',
            'payment_capture_date' => 'datetime',
            'acceptance_date' => 'datetime',
            'date_joined' => 'date',
            'date_of_engagement' => 'date',
            'last_payment_date' => 'date',
        ];
    }

    /**
     * Accessor to get masked salary display
     */
    public function getMaskedSalaryAttribute(): string
    {
        if ($this->basic_salary_hash) {
            return '******';
        }

        return $this->basic_salary ? number_format($this->basic_salary, 2) : 'N/A';
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id', 'id');
    }

    public function financeOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_officer_user_id', 'id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(StaffWelfareLoanApproval::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StaffWelfareLoanPayment::class);
    }
}
