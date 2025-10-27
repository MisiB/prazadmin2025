<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bankaccount extends Model
{
    public function currency():BelongsTo{
        return $this->BelongsTo(Currency::class, 'currency_id','id');
    }

    public function paynowintegration():BelongsTo{
        return $this->BelongsTo(Paynowintegration::class, 'id','bankaccount_id');
    }
}
