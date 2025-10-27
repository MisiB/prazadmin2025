<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issuelog extends Model
{
    protected $fillable = [
        'issuegroup_id',
        'issuetype_id',
        'department_id',
        'ticketnumber',
        'regnumber',
        'name',
        'email',
        'phone',
        'title',
        'description',
        'attachments',
        'status',
        'priority',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'assigned_at' => 'datetime',
        ];
    }

    public function issuegroup()
    {
        return $this->belongsTo(Issuegroup::class);
    }

    public function issuetype()
    {
        return $this->belongsTo(Issuetype::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedto()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedby()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function createdby()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(Issuecomment::class)->orderBy('created_at', 'desc');
    }
}
