<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Output extends Model
{
    //
    public function outcome():BelongsTo{
        return $this->belongsTo(Outcome::class);
    }
    public function departmentoutputs():HasMany{
        return $this->hasMany(Departmentoutput::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
    public function approver():BelongsTo{
        return $this->belongsTo(User::class,'approvedby','id');
    }
}
