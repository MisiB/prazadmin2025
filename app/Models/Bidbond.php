<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidbond extends Model
{
    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function tenderfee()
    {
        return $this->belongsTo(Tenderfee::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function refundschedulelists()
    {
        return $this->hasMany(Bidbondrefundschedulelist::class);
    }
    public function refundschedule()
    {
        return $this->belongsTo(Bidbondrefundschedule::class);
    }
}
