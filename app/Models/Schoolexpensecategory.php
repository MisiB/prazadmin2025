<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schoolexpensecategory extends Model
{
    use HasFactory;

    public function schoolmonthlyretuns():HasMany
    {
        return $this->hasMany(Schoolmonthlyreturn::class,'schoolexpensecategory_id','id');
    } 
}
