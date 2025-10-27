<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategysubprogramme extends Model
{
   
    public function strategysubprogrammeoutputs():HasMany{
        return $this->hasMany(Strategysubprogrammeoutput::class);
    }
    public function creator(){
        return $this->belongsTo(User::class,"createdby")->select("id","email");
    }
    public function approver(){
        return $this->belongsTo(User::class,"approvedby")->select("id","email");
    }
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function strategyprogrammeoutcome(){
        return $this->belongsTo(Strategyprogrammeoutcome::class,"programmeoutcome_id","id");
    }
      public function strategicsubprogrammeoutputindicator(){
        return $this->belongsTo(Strategyprogrammeoutcomeindicator::class,'programmeoutcomeindicator_id','id');
    }
}
