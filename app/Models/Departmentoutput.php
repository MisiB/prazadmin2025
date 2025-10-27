<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Departmentoutput extends Model
{
    //
    public function output():BelongsTo{
        return $this->belongsTo(Output::class);
    }
    public function indicators():HasMany{
        return $this->hasMany(Indicator::class);
    }
    public function department():BelongsTo{
        return $this->belongsTo(Department::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }   
}
