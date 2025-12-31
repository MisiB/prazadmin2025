<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TsAllowanceConfigAudit extends Model
{
    protected $fillable = [
        'ts_allowance_config_id',
        'previous_rate',
        'new_rate',
        'grade_band_id',
        'changed_by',
        'change_date',
        'effective_date',
        'approval_reference',
        'change_reason',
    ];

    protected function casts(): array
    {
        return [
            'previous_rate' => 'decimal:2',
            'new_rate' => 'decimal:2',
            'change_date' => 'datetime',
            'effective_date' => 'date',
        ];
    }

    public function allowanceConfig(): BelongsTo
    {
        return $this->belongsTo(TsAllowanceConfig::class);
    }

    public function gradeBand(): BelongsTo
    {
        return $this->belongsTo(GradeBand::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
