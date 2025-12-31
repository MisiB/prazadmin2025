<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeBand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function allowanceConfigs(): HasMany
    {
        return $this->hasMany(TsAllowanceConfig::class);
    }

    public function activeAllowanceConfigs(): HasMany
    {
        return $this->hasMany(TsAllowanceConfig::class)->where('status', 'ACTIVE');
    }
}
