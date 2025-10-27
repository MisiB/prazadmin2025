<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategyprogramme extends Model
{
    
    public function strategy()
    {
        return $this->belongsTo(Strategy::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'creator_id','id');
    }

    public function updator()
    {
        return $this->belongsTo(User::class,'updator_id','id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class,'approver_id','id');
    }

    public function outcomes()
    {
        return $this->hasMany(Strategyprogrammeoutcome::class,'programme_id','id');
    }
  
}
