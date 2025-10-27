<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receiverstoresrequisitionapproval extends Model
{
    public function storesrequisition():BelongsTo
    {
        return $this->belongsTo(Storesrequisition::class, 'storesrequisition_uuid', 'storesrequisition_uuid');
    }
}
