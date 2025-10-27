<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategyprogrammeoutcomeindicator extends Model
{
    
    public function subprogrammes(){
        return $this->hasMany(Strategysubprogramme::class,'programmeoutcomeindicator_id','id');
    }
    public function department(){
        return $this->belongsTo(Department::class,'department_id','id');
    }
}
