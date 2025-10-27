<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accounttype extends Model
{
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'accounttype_id', 'id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'accounttype_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
