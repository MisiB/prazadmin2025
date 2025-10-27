<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Targetmatrix extends Model
{
    //
   

    public function target():BelongsTo{
        return $this->belongsTo(Target::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
    public function individualworkplans():HasMany{
        return $this->hasMany(Individualworkplan::class);
    }
}
