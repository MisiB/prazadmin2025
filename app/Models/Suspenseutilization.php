<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspenseutilization extends Model
{
    public function invoice(){
        return $this->belongsTo(Invoice::class,"invoice_id");
    }
    public function suspense(){
        return $this->belongsTo(Suspense::class,"suspense_id");
    }
}
