<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function individualworkplan()
    {
        return $this->belongsTo(Individualworkplan::class);
    }

    public function recurringTasks()
    {
        return $this->hasMany(RecurringTask::class);
    }
}
