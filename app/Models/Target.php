<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Target extends Model
{
    protected $fillable = ['indicator_id', 'year', 'target', 'variance', 'status', 'createdby', 'approvedby'];

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }
    public function creator():BelongsTo{
        return $this->belongsTo(User::class,'createdby','id');
    }
    public function targetmatrices():HasMany{
        return $this->hasMany(Targetmatrix::class);
    }
}
