<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendarweek extends Model
{
    public function calendaryears(){
        return $this->belongsTo(Calendaryear::class, 'calendaryear_id');
    }

    public function calendardays(){
        return $this->hasMany(Calendarday::class, 'calendarweek_id');
    }
    public function calenderworkusertasks(){
        return $this->hasMany(Calenderworkusertask::class, 'calendarweek_id');
    }
}
