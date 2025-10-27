<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenuepostingjobitem extends Model
{
    public function revenuepostingjob()
    {
        return $this->belongsTo(Revenuepostingjob::class);
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
