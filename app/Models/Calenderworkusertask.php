<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calenderworkusertask extends Model
{
    public function calendarweek(){
        return $this->belongsTo(Calendarweek::class, 'calendarweek_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function supervisor(){
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
