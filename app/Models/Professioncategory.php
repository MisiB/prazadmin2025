<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Professioncategory extends Model
{
    
    public function documentrequirements():BelongsToMany{
        return $this->belongsToMany(Documentrequirement::class);
    }
}
