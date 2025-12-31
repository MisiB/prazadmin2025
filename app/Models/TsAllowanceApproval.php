<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TsAllowanceApproval extends Model
{
    protected $fillable = [
        'ts_allowance_id',
        'workflowparameter_id',
        'user_id',
        'status',
        'comment',
        'authorization_code_hash',
        'authorization_code_validated',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'authorization_code_validated' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function tsAllowance(): BelongsTo
    {
        return $this->belongsTo(TsAllowance::class);
    }

    public function workflowParameter(): BelongsTo
    {
        return $this->belongsTo(Workflowparameter::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
