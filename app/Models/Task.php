<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
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

    public function calendarday()
    {
        return $this->belongsTo(Calendarday::class, 'calendarday_id');
    }

    public function taskinstances()
    {
        return $this->hasMany(Taskinstance::class);
    }

    /**
     * Get the evidence file URL
     */
    public function getEvidenceUrlAttribute(): ?string
    {
        return $this->evidence_path ? asset('storage/'.$this->evidence_path) : null;
    }

    /**
     * Check if task has evidence attached
     */
    public function hasEvidence(): bool
    {
        return ! empty($this->evidence_path);
    }
}
