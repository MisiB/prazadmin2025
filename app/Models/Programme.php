<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Programme extends Model
{
    //
    public function strategy():BelongsTo{
        return $this->belongsTo(Strategy::class);
    }
    public function outcomes():HasMany{
        return $this->hasMany(Outcome::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
    public function approver():BelongsTo{
        return $this->belongsTo(User::class,'approvedby','id');
    }
}
