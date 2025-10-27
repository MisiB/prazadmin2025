<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payeedetail extends Model
{
    public function attempts()
    {
        return $this->hasMany(Payeeattempt::class, 'payeedetail_id');
    }
}
