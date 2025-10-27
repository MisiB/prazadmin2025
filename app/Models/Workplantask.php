<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workplantask extends Model
{
    public function workplan():BelongsTo{
        return $this->belongsTo(Workplan::class);
    }
    public function creator(){
        return $this->belongsTo(User::class,"createdby")->select("id","email");
    }
    public function approver(){
        return $this->belongsTo(User::class,"approvedby")->select("id","email");
    }
}
