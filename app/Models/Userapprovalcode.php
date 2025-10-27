<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Userapprovalcode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'expiry_date'
    ];

    protected $casts = [
        'expiry_date' => 'datetime'
    ];

    protected $appends = ['masked_code'];

    public function getMaskedCodeAttribute()
    {
        return str_repeat('*', 4) . substr($this->code, -4);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
