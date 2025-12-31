<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TsAllowanceConfig extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'grade_band_id',
        'currency_id',
        'out_of_station_subsistence_rate',
        'overnight_allowance_rate',
        'bed_allowance_rate',
        'breakfast_rate',
        'lunch_rate',
        'dinner_rate',
        'fuel_rate',
        'toll_gate_rate',
        'mileage_rate_per_km',
        'effective_from',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'out_of_station_subsistence_rate' => 'decimal:2',
            'overnight_allowance_rate' => 'decimal:2',
            'bed_allowance_rate' => 'decimal:2',
            'breakfast_rate' => 'decimal:2',
            'lunch_rate' => 'decimal:2',
            'dinner_rate' => 'decimal:2',
            'fuel_rate' => 'decimal:2',
            'toll_gate_rate' => 'decimal:2',
            'mileage_rate_per_km' => 'decimal:2',
            'effective_from' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function gradeBand(): BelongsTo
    {
        return $this->belongsTo(GradeBand::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(TsAllowanceConfigAudit::class);
    }
}
