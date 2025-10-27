<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Onlinepayment extends Model
{
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'invoicenumber', 'invoicenumber');
    }

    public function payeeattempts()
    {
        return $this->hasMany(Payeeattempt::class, 'onlinepayment_id');
    }
}
