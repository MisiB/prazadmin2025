<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenderfee extends Model
{
    
    public function currency(): BelongsTo{
        return $this->belongsTo(Currency::class);
    }
    public function inventoryitem(): BelongsTo{
        return $this->belongsTo(Inventoryitem::class);
    }
}
