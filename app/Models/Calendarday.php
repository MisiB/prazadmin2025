<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendarday extends Model
{
    public function calendarweeks()
    {
        return $this->belongsTo(Calendarweek::class, 'calendarweek_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function userTasks()
    {
        return $this->hasMany(Task::class)->with('taskinstances')->where('user_id', auth()->id());
    }
}
