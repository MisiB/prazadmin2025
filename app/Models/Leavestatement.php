<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leavestatement extends Model
{
    public function leavetype():BelongsTo
    {
        return $this->belongsTo(Leavetype::class, 'leavetype_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
