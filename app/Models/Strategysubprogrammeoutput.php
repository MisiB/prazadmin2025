<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategysubprogrammeoutput extends Model
{
    public function strategysubprogramme():BelongsTo{
        return $this->belongsTo(Strategysubprogramme::class);
    }
    public function individualoutputs():HasMany{
        return $this->hasMany(Individualoutput::class);
    }
    public function workplans():HasMany{
        return $this->hasMany(Workplan::class);
    }

    public function creator(){
        return $this->belongsTo(User::class,"createdby")->select("id","email");
    }
    public function approver(){
        return $this->belongsTo(User::class,"approvedby")->select("id","email");
    }
}
