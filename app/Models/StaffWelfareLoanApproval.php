<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffWelfareLoanApproval extends Model
{
    protected function casts(): array
    {
        return [
            'authorization_code_validated' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function staffWelfareLoan(): BelongsTo
    {
        return $this->belongsTo(StaffWelfareLoan::class);
    }

    public function workflowParameter(): BelongsTo
    {
        return $this->belongsTo(Workflowparameter::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
