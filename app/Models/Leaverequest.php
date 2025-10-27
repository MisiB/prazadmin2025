<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leaverequest extends Model
{
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function leavetype(): BelongsTo
    {
        return $this->belongsTo(Leavetype::class, 'leavetype_id', 'id');
    }

    public function leaverequestapproval(): BelongsTo
    {
        return $this->belongsTo(Leaverequestapproval::class, 'leaverequestuuid', 'leaverequest_uuid');
    }
    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actinghod_id', 'id');
    }
}
  