<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exchangerate extends Model
{
    public function primarycurrency():BelongsTo
    {
        return $this->belongsTo(Currency::class,'primary_currency_id');
    }
    public function secondarycurrency():BelongsTo
    {
        return $this->belongsTo(Currency::class,'secondary_currency_id');
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}