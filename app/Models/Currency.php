<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    public function paynowintegration():HasMany
    {
        return $this->hasMany(Paynowintegration::class, 'currency_id', 'id');
    }
    public function exchangerate():HasMany
    {
        return $this->hasMany(Exchangerate::class,"secondary_currency_id","id");
    }
}
