<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function individualworkplan()
    {
        return $this->belongsTo(Individualworkplan::class);
    }

    public function calendarday()
    {
        return $this->belongsTo(Calendarday::class, 'calendarday_id');
    }
}
