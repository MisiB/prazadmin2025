<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documentrequirement extends Model
{
    public function productdocumentrequirements():HasMany
    {
        return $this->hasMany(Productdocumentrequirement::class);
    }
}
