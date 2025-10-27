<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallettopup extends Model
{
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class,"initiatedby","id");
    }
    public function approver()
    {
        return $this->belongsTo(User::class,"approvedby","id");
    }
    public function banktransaction()
    {
        return $this->belongsTo(Banktransaction::class,"banktransaction_id","id");
    }
    public function linkeduser()
    {
        return $this->belongsTo(User::class,"linkedby","id");
    }
    public function suspense()
    {
        return $this->belongsTo(Suspense::class,"suspense_id","id");
    }
}
