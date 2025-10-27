<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issuecomment extends Model
{
    protected $fillable = [
        'issuelog_id',
        'user_email',
        'comment',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function issuelog()
    {
        return $this->belongsTo(Issuelog::class);
    }
}
