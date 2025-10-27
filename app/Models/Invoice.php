<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public function inventoryitem()
    {
        return $this->belongsTo(Inventoryitem::class,"inventoryitem_id");
    }
    public function currency(){
        return $this->belongsTo(Currency::class,"currency_id");
    }
    public function customer(){
        return $this->belongsTo(Customer::class,"customer_id");
    }
    public function receipts(){
        return $this->hasMany(Suspenseutilization::class,"invoice_id","id");
    }
}
