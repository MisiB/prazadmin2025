<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTaskReview extends Model
{
    protected $fillable = [
        'user_id',
        'calendarweek_id',
        'week_start_date',
        'week_end_date',
        'total_tasks',
        'completed_tasks',
        'incomplete_tasks',
        'completion_rate',
        'total_hours_planned',
        'total_hours_completed',
        'task_reviews',
        'overall_comment',
        'reviewed_at',
        'is_submitted',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'week_end_date' => 'date',
            'reviewed_at' => 'datetime',
            'task_reviews' => 'array',
            'is_submitted' => 'boolean',
            'completion_rate' => 'decimal:2',
            'total_hours_planned' => 'decimal:2',
            'total_hours_completed' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function calendarweek(): BelongsTo
    {
        return $this->belongsTo(Calendarweek::class);
    }

    public function calculateCompletionRate(): float
    {
        if ($this->total_tasks === 0) {
            return 0;
        }

        return round(($this->completed_tasks / $this->total_tasks) * 100, 2);
    }
}
