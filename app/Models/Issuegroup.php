<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issuegroup extends Model
{
    protected $fillable = ['name'];

    public function issuetypes()
    {
        return $this->hasMany(Issuetype::class);
    }

    public function issuelogs()
    {
        return $this->hasMany(Issuelog::class);
    }
}
