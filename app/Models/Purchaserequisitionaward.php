<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchaserequisitionaward extends Model
{
    
    public function purchaserequisition()
    {
        return $this->belongsTo(Purchaserequisition::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function documents()
    {
        return $this->hasMany(Purchaserequisitionawarddocument::class);
    }
    public function createdby()
    {
        return $this->belongsTo(User::class,"created_by");
    }
    public function approvedby()
    {
        return $this->belongsTo(User::class,"approved_by");
    }
}
