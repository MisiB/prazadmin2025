<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Approvalflow extends Model
{
    
    public function stages():HasMany{
        return $this->hasMany(Approvalflowstage::class);
    }
}
