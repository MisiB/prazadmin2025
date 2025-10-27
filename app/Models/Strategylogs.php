<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategylogs extends Model
{
    
    public function casts(): array
    {
        return [
            'old_data' => 'array',
            'new_data' => 'array',
        ];
    }
}
