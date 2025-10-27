<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bankreconciliation extends Model
{
     public function bankreconciliationdata(){
        return $this->hasMany(Bankreconciliationdata::class);
     }
     public function currency(){
        return $this->belongsTo(Currency::class);
     }
     public function bankaccount(){
        return $this->belongsTo(Bankaccount::class);
     }
     public function user(){
        return $this->belongsTo(User::class);
     }
}
