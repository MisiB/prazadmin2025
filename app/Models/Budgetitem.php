<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budgetitem extends Model
{
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function expensecategory()
    {
        return $this->belongsTo(Expensecategory::class);
    }

    public function strategysubprogrammeoutput(){
        return $this->belongsTo(Strategysubprogrammeoutput::class);
    }

    public function sourceoffund(){
        return $this->belongsTo(Sourceoffund::class);
    }

    public function createdby(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedby(){
        return $this->belongsTo(User::class,'updated_by');
    }
    public function approvedby(){
        return $this->belongsTo(User::class,'approved_by');
    }
    public function incomingvirements(){
        return $this->hasMany(Budgetvirement::class,'to_budgetitem_id');
    }
    public function outgoingvirements(){
        return $this->hasMany(Budgetvirement::class,'from_budgetitem_id');
    }

    public function purchaserequisitions(){
        return $this->hasMany(Purchaserequisition::class);
    }


}
