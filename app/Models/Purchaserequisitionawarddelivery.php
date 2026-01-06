<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchaserequisitionawarddelivery extends Model
{
    public function purchaserequisitionaward()
    {
        return $this->belongsTo(Purchaserequisitionaward::class);
    }

    public function deliveredby()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
