<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchaserequisitionapproval extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function workflowparameter(): BelongsTo
    {
        return $this->belongsTo(Workflowparameter::class);
    }
}
