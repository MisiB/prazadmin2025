<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendaryear extends Model
{
    public function calendarweeks(){    
        return $this->hasMany(Calendarweek::class, 'calendaryear_id');
    }
}
