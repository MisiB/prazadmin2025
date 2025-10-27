<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paynowintegration extends Model
{
    public function currency():BelongsTo{
        return $this->belongsTo(Currency::class);
    }
    public function bankaccount():BelongsTo{
        return $this->belongsTo(Bankaccount::class);
    }
}
