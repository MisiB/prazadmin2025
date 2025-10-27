<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategyprogrammeoutcome extends Model
{
     public function programme():BelongsTo{
        return $this->belongsTo(Strategyprogramme::class);
     }
     public function indicators():HasMany{
        return $this->hasMany(Strategyprogrammeoutcomeindicator::class,'programmeoutcome_id','id');
     }
   
}
