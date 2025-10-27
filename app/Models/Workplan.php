<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workplan extends Model
{
    public function individualoutput():BelongsTo{
        return $this->belongsTo(Individualoutput::class);
    }
    public function tasks():HasMany{
        return $this->hasMany(Workplantask::class);
    }
    public function creator(){
        return $this->belongsTo(User::class,"createdby")->select("id","email");
    }
    public function approver(){
        return $this->belongsTo(User::class,"approvedby")->select("id","email");
    }
}
