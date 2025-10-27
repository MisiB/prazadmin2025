<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Outcome extends Model
{
    //
    public function programme():BelongsTo{
        return $this->belongsTo(Programme::class);
    }
    public function outputs():HasMany{
        return $this->hasMany(Output::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
    public function approver():BelongsTo{
        return $this->belongsTo(User::class,'approvedby','id');
    }
}
