<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payeeattempt extends Model
{
    public function payeedetail()
    {
        return $this->belongsTo(Payeedetail::class, 'payeedetail_id');
    }

    public function onlinepayment()
    {
        return $this->belongsTo(Onlinepayment::class, 'onlinepayment_id');
    }
}
