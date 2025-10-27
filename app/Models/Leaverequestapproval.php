<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leaverequestapproval extends Model
{
    public function leaverequest():BelongsTo
    {
        return $this->belongsTo(Leaverequest::class, 'leaverequest_uuid', 'leaverequestuuid');
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
