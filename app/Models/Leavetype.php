<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Leavetype extends Model
{
    public function leavestatements(): BelongsToMany
    {
        return $this->belongsToMany(Leavestatement::class, 'id', 'leavetype_id');
    }
    public function leaverequests():BelongsToMany
    {
        return $this->belongsToMany(Leaverequest::class, 'id', 'leavetype_id');
    }
}
