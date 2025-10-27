<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenuepostingjob extends Model
{
    public function inventoryitem()
    {
        return $this->belongsTo(Inventoryitem::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function revenuepostingjobitems()
    {
        return $this->hasMany(Revenuepostingjobitem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by','id');
    }
}
