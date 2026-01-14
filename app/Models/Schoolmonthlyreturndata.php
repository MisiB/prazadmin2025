<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schoolmonthlyreturndata extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolmonthlyretun_id',
        'item_description',
        'quantity',
        'unit_price',
        'total_price',
        'currency_id',
        'sourceoffund',
        'amount'
    ];
    public function currency():BelongsTo{
        return $this->belongsTo(Currency::class,'currency_id','id');
    }
}
