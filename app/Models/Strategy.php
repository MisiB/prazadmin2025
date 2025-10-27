<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategy extends Model
{
    
    public function  programmes():HasMany{
        return $this->hasMany(Programme::class);
    }
    public function approver():BelongsTo{
        return $this->belongsTo(User::class,'approvedby','id');
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
}
