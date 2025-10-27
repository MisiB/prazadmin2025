<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Individualoutput extends Model
{
    public function strategysubprogrammeoutput():BelongsTo{
        return $this->belongsTo(Strategysubprogrammeoutput::class);
    }
    public function parent():BelongsTo{
        return $this->belongsTo(Individualoutput::class,"parent_id");
    }
    public function  children():HasMany{
        return $this->hasMany(Individualoutput::class,"parent_id");
    }
    public function descendants(){
        return $this->children()->with("descendants");
    }
    public function  ancestors(){
        return $this->parent()->with("ancestors");
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
    public function assignee(){
        return $this->hasMany(Individualoutputassignee::class,"individualoutput_id","id");
    }

}
