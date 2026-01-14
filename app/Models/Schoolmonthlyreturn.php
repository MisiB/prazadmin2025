<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schoolmonthlyreturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'schoolexpensecategory_id',
        'currency_id',
        'sourceoffund',
        'year',
        'month',
    ];

    public function school()
    {
        return $this->belongsTo(School::class,'school_id','id');
    }

    public function schoolexpensecategory()
    {
        return $this->belongsTo(schoolexpensecategory::class,'schoolexpensecategory_id','id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id','id');
    }

    public function schoolmonthlyreturndatas()
    {
        return $this->hasMany(schoolmonthlyreturndata::class,'schoolmonthlyretun_id','id');
    }
}
