<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tender extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function tendertype(): BelongsTo
    {
        return $this->belongsTo(Tendertype::class);
    }

    public function tenderfees()
    {
        return $this->hasMany(Tenderfee::class);
    }
    protected function casts(): array
    {
        return [
            'suppliercategories' => 'array',
        ];
    }
}
