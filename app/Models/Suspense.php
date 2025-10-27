<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspense extends Model
{
    public function banktransaction()
    {
        return $this->belongsTo(Banktransaction::class,"source_id","id");
    }
    public function onlinepayment(){
        return $this->belongsTo(Onlinepayment::class,"source_id","id");
    }
    public function customer(){
        return $this->belongsTo(Customer::class,"customer_id");
    }
    public function wallettopup(){
        return $this->belongsTo(Wallettopup::class,"source_id","id");
    }
    public function suspenseutilizations(){
        return $this->hasMany(Suspenseutilization::class,"suspense_id","id");
    }
}
