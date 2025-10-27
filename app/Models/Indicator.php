<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Indicator extends Model
{
    //
    public function departmentoutput():BelongsTo{
        return $this->belongsTo(Departmentoutput::class);
    }
    public function targets():HasMany{
        return $this->hasMany(Target::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
    public function approver():BelongsTo{
        return $this->belongsTo(User::class,'approvedby','id');
    }
}
