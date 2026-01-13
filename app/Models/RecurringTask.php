<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurringtask extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_created_date' => 'date',
        'next_create_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taskTemplate()
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    public function individualworkplan()
    {
        return $this->belongsTo(Individualworkplan::class);
    }
}
