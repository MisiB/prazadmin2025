<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bankreconciliationdata extends Model
{
    
    public function bankreconciliation(){
        return $this->belongsTo(Bankreconciliation::class);
    }
    public function banktransaction(){
        return $this->belongsTo(Banktransaction::class);
    }
}
