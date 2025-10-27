<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    public function submodules(): HasMany
    {
        return $this->hasMany(Submodule::class, 'module_id', 'id');
    }
}
