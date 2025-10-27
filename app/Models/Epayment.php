<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Epayment extends Model
{
    public function invoice(){
        return $this->belongsTo(Invoice::class);
    }
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function onlinepayment(){
        return $this->belongsTo(Onlinepayment::class);
    }
}
