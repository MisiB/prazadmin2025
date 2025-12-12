<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Taskinstance extends Model
{
    protected $fillable = [
        'task_id',
        'date',
        'planned_hours',
        'worked_hours',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'planned_hours' => 'decimal:2',
            'worked_hours' => 'decimal:2',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
