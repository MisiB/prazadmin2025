<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Approvalflowstage extends Model
{
    public function users():HasManyThrough{
        return $this->hasManyThrough(User::class, Approvalflowstageuser::class);
    }
}
