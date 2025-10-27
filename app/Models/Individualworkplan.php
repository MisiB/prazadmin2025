<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Individualworkplan extends Model
{
    protected $fillable = [
        'strategy_id',
        'year',
        'approver_id',
        'user_id',
        'targetmatrix_id',
        'month',
        'output',
        'indicator',
        'weightage',
        'target',
        'status',
        'approved_at',
        'remarks',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function targetmatrix(): BelongsTo
    {
        return $this->belongsTo(Targetmatrix::class, 'targetmatrix_id', 'id');
    }
}
