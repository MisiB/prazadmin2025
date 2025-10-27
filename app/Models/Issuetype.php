<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issuetype extends Model
{
    protected $fillable = ['name', 'department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function issuelogs()
    {
        return $this->hasMany(Issuelog::class);
    }
}
