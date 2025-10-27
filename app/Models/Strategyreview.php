<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategyreview extends Model
{
    public function strategy():BelongsTo{
        return $this->belongsTo(Strategy::class);
    }
    public function strategyoutcome():HasMany{
        return $this->hasMany(Strategyoutcome::class);
    }
    public function creator(){
        return $this->belongsTo(User::class,"createdby")->select("id","email");
    }
    public function approver(){
        return $this->belongsTo(User::class,"approvedby")->select("id","email");
    }
}
