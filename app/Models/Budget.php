<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    public  function createdby(){
        return $this->belongsTo(User::class,"created_by");
    }
    public  function updatedby(){
        return $this->belongsTo(User::class,"updated_by");
    }
    public  function deletedby(){
        return $this->belongsTo(User::class,"deleted_by");
    }
    public  function approvedby(){
        return $this->belongsTo(User::class,"approved_by");
    }
    public  function currency(){
        return $this->belongsTo(Currency::class,"currency_id");
    }

    public function budgetitems(){
        return $this->hasMany(Budgetitem::class,"budget_id");
    }
    public function budgetvirements(){
        return $this->hasMany(Budgetvirement::class,"budget_id");
    }
}
