<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TsAllowance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'workflow_id',
        'application_number',
        'status',
        'full_name',
        'job_title',
        'department_id',
        'grade',
        'grade_band_id',
        'trip_start_date',
        'trip_end_date',
        'reason_for_allowances',
        'trip_attachment_path',
        'applicant_digital_signature',
        'submission_date',
        'applicant_user_id',
        'out_of_station_subsistence',
        'overnight_allowance',
        'bed_allowance',
        'bed_option',
        'bed_nights',
        'breakfast',
        'breakfast_option',
        'breakfast_days',
        'lunch',
        'lunch_option',
        'lunch_days',
        'dinner',
        'dinner_option',
        'dinner_days',
        'fuel',
        'toll_gates',
        'mileage_estimated_distance',
        'calculated_subtotal',
        'balance_due',
        'number_of_days',
        'recommendation_decision',
        'hod_name',
        'hod_designation',
        'hod_digital_signature',
        'recommendation_date',
        'hod_user_id',
        'hod_comment',
        'approval_decision',
        'ceo_digital_signature',
        'approval_date',
        'ceo_user_id',
        'ceo_comment',
        'verified_allowance_rates',
        'verified_total_amount',
        'exchange_rate_id',
        'exchange_rate_applied',
        'finance_officer_name',
        'finance_digital_signature',
        'verification_date',
        'finance_officer_user_id',
        'finance_comment',
        'currency_id',
        'amount_paid_usd',
        'amount_paid_original',
        'payment_method',
        'payment_reference',
        'payment_date',
        'proof_of_payment_path',
        'payment_capture_date',
        'payment_notes',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'out_of_station_subsistence' => 'decimal:2',
            'overnight_allowance' => 'decimal:2',
            'bed_allowance' => 'decimal:2',
            'breakfast' => 'decimal:2',
            'lunch' => 'decimal:2',
            'dinner' => 'decimal:2',
            'fuel' => 'decimal:2',
            'toll_gates' => 'decimal:2',
            'mileage_estimated_distance' => 'decimal:2',
            'calculated_subtotal' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'verified_total_amount' => 'decimal:2',
            'exchange_rate_applied' => 'decimal:4',
            'amount_paid_usd' => 'decimal:2',
            'amount_paid_original' => 'decimal:2',
            'trip_start_date' => 'date',
            'trip_end_date' => 'date',
            'payment_date' => 'date',
            'submission_date' => 'datetime',
            'recommendation_date' => 'datetime',
            'approval_date' => 'datetime',
            'verification_date' => 'datetime',
            'payment_capture_date' => 'datetime',
            'applicant_digital_signature' => 'boolean',
            'hod_digital_signature' => 'boolean',
            'ceo_digital_signature' => 'boolean',
            'finance_digital_signature' => 'boolean',
            'verified_allowance_rates' => 'array',
            'comments' => 'collection',
        ];
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

    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_user_id', 'id');
    }

    public function ceo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ceo_user_id', 'id');
    }

    public function financeOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_officer_user_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(Exchangerate::class);
    }

    public function gradeBand(): BelongsTo
    {
        return $this->belongsTo(GradeBand::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TsAllowanceApproval::class);
    }
}
